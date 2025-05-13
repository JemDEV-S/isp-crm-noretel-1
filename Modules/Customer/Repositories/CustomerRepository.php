<?php

namespace Modules\Customer\Repositories;

use Modules\Core\Repositories\BaseRepository;
use Modules\Customer\Interfaces\CustomerRepositoryInterface;
use Modules\Customer\Entities\Customer;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    /**
     * CustomerRepository constructor.
     *
     * @param Customer $model
     */
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEmail($email)
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdentityDocument($document)
    {
        return $this->model->where('identity_document', $document)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getByType($type, $perPage = 15)
    {
        return $this->model->where('customer_type', $type)->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getBySegment($segment, $perPage = 15)
    {
        return $this->model->where('segment', $segment)->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function search($search, $perPage = 15)
    {
        return $this->model->where(function ($query) use ($search) {
            $query->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('identity_document', 'like', "%{$search}%");
        })->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getWithFilters(array $filters, $perPage = 15)
    {
        $query = $this->model->query();

        if (isset($filters['type'])) {
            $query->where('customer_type', $filters['type']);
        }

        if (isset($filters['segment'])) {
            $query->where('segment', $filters['segment']);
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('identity_document', 'like', "%{$search}%");
            });
        }

        if (isset($filters['date_from'])) {
            $query->where('registration_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('registration_date', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage);
    }
        public function getActiveCustomers()
    {
        return $this->model->where('active', true)->get();
    }
}