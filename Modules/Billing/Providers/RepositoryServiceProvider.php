<?php

namespace Modules\Billing\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Billing\Repositories\InvoiceRepository;
use Modules\Billing\Repositories\PaymentRepository;
use Modules\Billing\Entities\Invoice;
use Modules\Billing\Entities\Payment;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Registrar repositorios
        $this->app->bind(InvoiceRepository::class, function ($app) {
            return new InvoiceRepository(new Invoice());
        });

        $this->app->bind(PaymentRepository::class, function ($app) {
            return new PaymentRepository(new Payment());
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            InvoiceRepository::class,
            PaymentRepository::class,
        ];
    }
}