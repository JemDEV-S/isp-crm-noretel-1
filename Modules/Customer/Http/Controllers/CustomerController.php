<?php

namespace Modules\Customer\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Customer\Services\CustomerService;
use Modules\Customer\Http\Requests\StoreCustomerRequest;
use Modules\Customer\Http\Requests\UpdateCustomerRequest;
use Modules\Customer\Entities\Customer;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * @var CustomerService
     */
    protected $customerService;

    /**
     * CustomerController constructor.
     *
     * @param CustomerService $customerService
     */
    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'search', 'type', 'segment', 'active', 'date_from', 'date_to'
        ]);
        
        $perPage = $request->get('per_page', 15);
        
        $customers = $this->customerService->searchCustomers($filters, $perPage);
        
        return view('customer::customers.index', compact('customers', 'filters'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $customerTypes = [
            'individual' => 'Individual',
            'business' => 'Empresa'
        ];
        
        $segments = [
            'residential' => 'Residencial',
            'business' => 'Empresarial',
            'corporate' => 'Corporativo',
            'public' => 'Sector Público'
        ];
        
        return view('customer::customers.create', compact('customerTypes', 'segments'));
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreCustomerRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCustomerRequest $request)
    {
        $customerData = $request->except(['addresses', 'emergency_contacts']);
        $addressesData = $request->input('addresses', []);
        
        $result = $this->customerService->createCustomer(
            $customerData,
            $addressesData,
            Auth::id(),
            $request->ip()
        );
        
        if (!$result['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);
        }
        
        return redirect()->route('customer.customers.show', $result['customer']->id)
            ->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $customer = Customer::with(['addresses', 'emergencyContacts', 'documents', 'leads'])
            ->findOrFail($id);
        
        return view('customer::customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $customer = Customer::with(['addresses', 'emergencyContacts'])
            ->findOrFail($id);
        
        $customerTypes = [
            'individual' => 'Individual',
            'business' => 'Empresa'
        ];
        
        $segments = [
            'residential' => 'Residencial',
            'business' => 'Empresarial',
            'corporate' => 'Corporativo',
            'public' => 'Sector Público'
        ];
        
        return view('customer::customers.edit', compact('customer', 'customerTypes', 'segments'));
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateCustomerRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCustomerRequest $request, $id)
    {
        $customerData = $request->except(['addresses', 'emergency_contacts']);
        $addressesData = $request->input('addresses', []);
        
        $result = $this->customerService->updateCustomer(
            $id,
            $customerData,
            $addressesData,
            Auth::id(),
            $request->ip()
        );
        
        if (!$result['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);
        }
        
        return redirect()->route('customer.customers.show', $id)
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $result = $this->customerService->deleteCustomer(
            $id,
            Auth::id(),
            $request->ip()
        );
        
        if (!$result['success']) {
            return redirect()->back()
                ->with('error', $result['message']);
        }
        
        return redirect()->route('customer.customers.index')
            ->with('success', $result['message']);
    }

    /**
     * Activate a customer.
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function activate($id, Request $request)
    {
        $result = $this->customerService->changeStatus(
            $id,
            true,
            Auth::id(),
            $request->ip()
        );
        
        if (!$result['success']) {
            return redirect()->back()
                ->with('error', $result['message']);
        }
        
        return redirect()->back()
            ->with('success', $result['message']);
    }

    /**
     * Deactivate a customer.
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function deactivate($id, Request $request)
    {
        $result = $this->customerService->changeStatus(
            $id,
            false,
            Auth::id(),
            $request->ip()
        );
        
        if (!$result['success']) {
            return redirect()->back()
                ->with('error', $result['message']);
        }
        
        return redirect()->back()
            ->with('success', $result['message']);
    }
}