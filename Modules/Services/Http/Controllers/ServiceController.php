<?php

namespace Modules\Services\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Services\Services\ServiceService;
use Modules\Services\Http\Requests\ServiceRequest;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    /**
     * @var ServiceService
     */
    protected $serviceService;

    /**
     * ServiceController constructor.
     *
     * @param ServiceService $serviceService
     */
    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $result = $this->serviceService->getAllServices();

        return view('services::services.index', [
            'services' => $result['services']
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $serviceTypes = [
            'internet' => 'Internet',
            'voip' => 'VoIP',
            'tv' => 'Televisión',
            'hosting' => 'Hosting',
            'cloud' => 'Cloud',
            'other' => 'Otro'
        ];

        $technologies = [
            'fiber' => 'Fibra Óptica',
            'wireless' => 'Inalámbrico',
            'cable' => 'Cable Coaxial',
            'dsl' => 'DSL',
            'satellite' => 'Satélite',
            'other' => 'Otro'
        ];

        return view('services::services.create', compact('serviceTypes', 'technologies'));
    }

    /**
     * Store a newly created resource in storage.
     * @param ServiceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ServiceRequest $request)
    {
        $result = $this->serviceService->createService(
            $request->validated(),
            $request->ip()
        );

        if (!$result['success']) {
            return redirect()->back()->withErrors(['message' => $result['message']])->withInput();
        }

        return redirect()->route('services.services.index')
            ->with('success', $result['message']);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $service = $this->serviceService->serviceRepository->find($id);

        $plans = $service->plans;
        $additionalServices = $service->additionalServices;

        return view('services::services.show', compact('service', 'plans', 'additionalServices'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $service = $this->serviceService->serviceRepository->find($id);

        $serviceTypes = [
            'internet' => 'Internet',
            'voip' => 'VoIP',
            'tv' => 'Televisión',
            'hosting' => 'Hosting',
            'cloud' => 'Cloud',
            'other' => 'Otro'
        ];

        $technologies = [
            'fiber' => 'Fibra Óptica',
            'wireless' => 'Inalámbrico',
            'cable' => 'Cable Coaxial',
            'dsl' => 'DSL',
            'satellite' => 'Satélite',
            'other' => 'Otro'
        ];

        return view('services::services.edit', compact('service', 'serviceTypes', 'technologies'));
    }

    /**
     * Update the specified resource in storage.
     * @param ServiceRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ServiceRequest $request, $id)
    {
        $result = $this->serviceService->updateService(
            $id,
            $request->validated(),
            $request->ip()
        );

        if (!$result['success']) {
            return redirect()->back()->withErrors(['message' => $result['message']])->withInput();
        }

        return redirect()->route('services.services.index')
            ->with('success', $result['message']);
    }

    /**
     * Activate the specified resource in storage.
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate($id, Request $request)
    {
        $result = $this->serviceService->toggleServiceStatus(
            $id,
            true,
            $request->ip()
        );

        if (!$result['success']) {
            return redirect()->back()->withErrors(['message' => $result['message']]);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Deactivate the specified resource in storage.
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deactivate($id, Request $request)
    {
        $result = $this->serviceService->toggleServiceStatus(
            $id,
            false,
            $request->ip()
        );

        if (!$result['success']) {
            return redirect()->back()->withErrors(['message' => $result['message']]);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id, Request $request)
    {
        $result = $this->serviceService->deleteService(
            $id,
            $request->ip()
        );

        if (!$result['success']) {
            return redirect()->back()->withErrors(['message' => $result['message']]);
        }

        return redirect()->route('services.services.index')
            ->with('success', $result['message']);
    }
}
