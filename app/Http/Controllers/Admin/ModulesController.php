<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class ModulesController extends Controller
{
    public function index(): Response
    {
        if (! Schema::hasTable('modules')) {
            return Inertia::render('admin/Modules', [
                'modules' => [],
            ]);
        }

        $modules = Module::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Module $module) => [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
                'enabled' => $module->enabled,
                'version' => $module->version,
                'description' => $module->description,
            ]);

        return Inertia::render('admin/Modules', [
            'modules' => $modules,
        ]);
    }

    public function update(Request $request, Module $module): RedirectResponse
    {
        if (! Schema::hasTable('modules')) {
            abort(503, 'Modules table is missing.');
        }

        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $module->update(['enabled' => $validated['enabled']]);

        ActivityLogger::log(
            $module->enabled ? 'module.enabled' : 'module.disabled',
            $module,
            $module->enabled ? 'Module enabled' : 'Module disabled',
        );

        return back()->with('status', 'Module updated.');
    }
}
