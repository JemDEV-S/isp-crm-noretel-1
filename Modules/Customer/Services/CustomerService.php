<?php

namespace Modules\Customer\Services;

use Modules\Customer\Interfaces\CustomerRepositoryInterface;
use Modules\Customer\Interfaces\AddressRepositoryInterface;
use Modules\Core\Entities\AuditLog;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * CustomerService constructor.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * Create a new customer with addresses
     *
     * @param array $customerData
     * @param array $addressesData
     * @param int $createdBy
     * @param string $ip
     * @return array
     */
    public function createCustomer(array $customerData, array $addressesData, $createdBy, $ip)
    {
        DB::beginTransaction();

        try {
            // Set registration date if not provided
            if (!isset($customerData['registration_date'])) {
                $customerData['registration_date'] = now();
            }
            
            // Create customer
            $customer = $this->customerRepository->create($customerData);
            
            // Create addresses
            $primarySet = false;
            foreach ($addressesData as $addressData) {
                $addressData['customer_id'] = $customer->id;
                
                // Ensure only one primary address
                if (isset($addressData['is_primary']) && $addressData['is_primary']) {
                    if ($primarySet) {
                        $addressData['is_primary'] = false;
                    } else {
                        $primarySet = true;
                    }
                }
                
                $this->addressRepository->create($addressData);
            }
            
            // Register audit
            AuditLog::register(
                $createdBy,
                'customer_created',
                'customer',
                "Cliente creado: {$customer->full_name}",
                $ip,
                null,
                $customer->toArray()
            );
            
            DB::commit();
            
            return [
                'success' => true,
                'customer' => $customer
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Error al crear el cliente: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update a customer and its addresses
     *
     * @param int $customerId
     * @param array $customerData
     * @param array $addressesData
     * @param int $updatedBy
     * @param string $ip
     * @return array
     */
    public function updateCustomer($customerId, array $customerData, array $addressesData, $updatedBy, $ip)
    {
        DB::beginTransaction();

        try {
            // Get customer before update for audit
            $oldData = $this->customerRepository->find($customerId)->toArray();
            
            // Update customer
            $this->customerRepository->update($customerId, $customerData);
            $customer = $this->customerRepository->find($customerId);
            
            // Handle addresses (create, update, delete)
            if (!empty($addressesData)) {
                $this->handleAddressesUpdate($customerId, $addressesData);
            }
            
            // Register audit
            AuditLog::register(
                $updatedBy,
                'customer_updated',
                'customer',
                "Cliente actualizado: {$customer->full_name}",
                $ip,
                $oldData,
                $customer->toArray()
            );
            
            DB::commit();
            
            return [
                'success' => true,
                'customer' => $customer
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Error al actualizar el cliente: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Change customer status
     *
     * @param int $customerId
     * @param bool $active
     * @param int $updatedBy
     * @param string $ip
     * @return array
     */
    public function changeStatus($customerId, $active, $updatedBy, $ip)
    {
        // Get customer before update for audit
        $customer = $this->customerRepository->find($customerId);
        $oldStatus = $customer->active;
        
        // Update status
        $this->customerRepository->update($customerId, ['active' => $active]);
        
        // Register audit
        $action = $active ? 'customer_activated' : 'customer_deactivated';
        $detail = $active ? "Cliente activado: {$customer->full_name}" : "Cliente desactivado: {$customer->full_name}";
        
        AuditLog::register(
            $updatedBy,
            $action,
            'customer',
            $detail,
            $ip,
            ['active' => $oldStatus],
            ['active' => $active]
        );
        
        return [
            'success' => true,
            'message' => $active ? 'Cliente activado correctamente.' : 'Cliente desactivado correctamente.'
        ];
    }

    /**
     * Delete a customer
     *
     * @param int $customerId
     * @param int $deletedBy
     * @param string $ip
     * @return array
     */
    public function deleteCustomer($customerId, $deletedBy, $ip)
    {
        // Get customer data for audit
        $customer = $this->customerRepository->find($customerId);
        
        if (!$customer) {
            return [
                'success' => false,
                'message' => 'Cliente no encontrado.'
            ];
        }
        
        $customerData = $customer->toArray();
        
        // Check if customer can be deleted (no contracts, etc.)
        if ($customer->hasActiveContracts()) {
            return [
                'success' => false,
                'message' => 'No se puede eliminar un cliente con contratos activos.'
            ];
        }
        
        DB::beginTransaction();
        
        try {
            // Delete customer (addresses, documents, etc. will be deleted by cascade)
            $this->customerRepository->delete($customerId);
            
            // Register audit
            AuditLog::register(
                $deletedBy,
                'customer_deleted',
                'customer',
                "Cliente eliminado: {$customer->full_name}",
                $ip,
                $customerData,
                null
            );
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Cliente eliminado correctamente.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Error al eliminar el cliente: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Search customers
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function searchCustomers(array $filters, $perPage = 15)
    {
        return $this->customerRepository->getWithFilters($filters, $perPage);
    }

    /**
     * Handle addresses update
     *
     * @param int $customerId
     * @param array $addressesData
     * @return void
     */
    protected function handleAddressesUpdate($customerId, array $addressesData)
    {
        // Get existing addresses
        $existingAddresses = $this->addressRepository->getByCustomer($customerId);
        $existingIds = $existingAddresses->pluck('id')->toArray();
        
        // Track which addresses are primary
        $primaryFound = false;
        
        foreach ($addressesData as $addressData) {
            // If ID is provided, update existing address
            if (isset($addressData['id']) && in_array($addressData['id'], $existingIds)) {
                $addressId = $addressData['id'];
                unset($addressData['id']);
                
                // Handle primary flag
                if (isset($addressData['is_primary']) && $addressData['is_primary']) {
                    if ($primaryFound) {
                        $addressData['is_primary'] = false;
                    } else {
                        $primaryFound = true;
                    }
                }
                
                $this->addressRepository->update($addressId, $addressData);
            } else {
                // Create new address
                $addressData['customer_id'] = $customerId;
                
                // Handle primary flag
                if (isset($addressData['is_primary']) && $addressData['is_primary']) {
                    if ($primaryFound) {
                        $addressData['is_primary'] = false;
                    } else {
                        $primaryFound = true;
                    }
                }
                
                $this->addressRepository->create($addressData);
            }
        }
        
        // Ensure at least one primary address
        if (!$primaryFound && $existingAddresses->count() > 0) {
            $firstAddress = $existingAddresses->first();
            $this->addressRepository->update($firstAddress->id, ['is_primary' => true]);
        }
    }
}