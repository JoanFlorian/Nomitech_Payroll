<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\Permission;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RoleManagementController extends Controller
{
    public function index()
    {
        $id_empresa = session('empresa_id');
        
        if (!$id_empresa) {
            return redirect()->route('empresa.select');
        }

        $roles = Rol::whereNotIn('id_rol', [1, 3, 4])
            ->withCount(['usuarios' => function($q) use ($id_empresa) {
                $q->where(function($query) use ($id_empresa) {
                    $query->whereHas('empresa', function($e) use ($id_empresa) {
                        $e->where('empresa.id_empresa', $id_empresa);
                    })->orWhereHas('contratos', function($c) use ($id_empresa) {
                        $c->where('id_empresa', $id_empresa);
                    });
                });
            }])->get();

        $permissionsByModule = Permission::all()->groupBy('module');

        return view('admin.roles.index', compact('roles', 'permissionsByModule'));
    }

    /**
     * Obtener empleados por rol y empresa activa.
     */
    public function getEmployeesByRole(Request $request)
    {
        $id_empresa = session('empresa_id');
        $rol_id = $request->rol_id ?? $request->role_id; // Support both names

        $employees = Usuario::where('id_rol', $rol_id)
            ->where(function($query) use ($id_empresa) {
                $query->whereHas('empresa', function($e) use ($id_empresa) {
                    $e->where('empresa.id_empresa', $id_empresa);
                })->orWhereHas('contratos', function($c) use ($id_empresa) {
                    $c->where('id_empresa', $id_empresa);
                });
            })
            ->get(['doc', 'doc as id', 'primer_nombre', 'primer_apellido', 'otros_nombres', 'segundo_apellido', 'is_owner', 'id_rol']);

        return response()->json($employees);
    }

    /**
     * Obtener permisos de un empleado con estado de herencia.
     */
    public function getPermissionsByEmployee(Request $request)
    {
        $id_empresa = session('empresa_id');
        $user_id = $request->id_usuario; // Match frontend parameter name
        $user = Usuario::with('rol.permissions')->findOrFail($user_id);

        $rolePermissions = $user->rol ? $user->rol->permissions->pluck('id')->toArray() : [];
        
        $overrides = DB::table('user_permissions')
            ->where('user_id', $user_id)
            ->where('company_id', $id_empresa)
            ->get()
            ->keyBy('permission_id');

        $allPermissions = Permission::all()->groupBy('module');
        
        $result = [];
        foreach ($allPermissions as $module => $perms) {
            $modulePerms = [];
            foreach ($perms as $p) {
                $isInherited = in_array($p->id, $rolePermissions);
                $override = $overrides->get($p->id);
                
                $active = $isInherited;
                if ($override) {
                    $active = (bool)$override->active;
                }

                $modulePerms[] = [
                    'id' => $p->id,
                    'name' => $p->display_name ?? $p->name,
                    'inherited' => $isInherited,
                    'has_override' => isset($override),
                    'active' => $active
                ];
            }
            $result[] = [
                'module' => $module,
                'permissions' => $modulePerms
            ];
        }

        return response()->json($result);
    }

    public function getPaginatedUsers(Request $request)
    {
        $id_empresa = session('empresa_id');
        $search = $request->get('q');

        $query = Usuario::whereHas('contratos', function($query) use ($id_empresa) {
                $query->where('id_empresa', $id_empresa);
            })
            ->whereHas('roles', function($q) use ($id_empresa) {
                $q->where('company_id', $id_empresa)
                  ->whereIn('name', ['Auxiliar de Nómina', 'Auditor de Nómina', 'Administrador']);
            });

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('primer_nombre', 'like', "%{$search}%")
                  ->orWhere('primer_apellido', 'like', "%{$search}%")
                  ->orWhere('doc', 'like', "%{$search}%");
            });
        }

        $users = $query->with(['roles' => function($q) use ($id_empresa) {
                $q->where('company_id', $id_empresa)->with('permissions');
            }])
            ->select('doc', 'primer_nombre', 'primer_apellido', 'otros_nombres', 'segundo_apellido', 'id_rol')
            ->paginate(7);

        $mappedUsers = collect($users->items())->map(function($user) use ($id_empresa) {
            $rbacRole = $user->roles->where('company_id', $id_empresa)->first();
            $rolePermissions = $rbacRole ? $rbacRole->permissions->pluck('id')->unique()->values() : collect([]);

            return [
                'id' => $user->doc,
                'name' => $user->nombre_completo,
                'role_id' => $rbacRole ? $rbacRole->id : null,
                'role_name' => $rbacRole ? $rbacRole->name : 'Sin Rol',
                'role_permissions' => $rolePermissions,
                'permissions' => $user->directPermissions()
                    ->where('company_id', $id_empresa)
                    ->get()
                    ->mapWithKeys(fn($p) => [$p->id => $p->pivot->active])
            ];
        });

        return response()->json([
            'data' => $mappedUsers,
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'total' => $users->total()
        ]);
    }


    public function updatePermission(Request $request)
    {
        $request->validate([
            'rol_id' => 'required',
            'permission_id' => 'required',
            'active' => 'required|boolean'
        ]);

        $id_empresa = session('empresa_id');
        $role = Rol::where('id_rol', $request->rol_id)->firstOrFail();
        $permission = Permission::findOrFail($request->permission_id);

        if ($request->active) {
            $role->permissions()->syncWithoutDetaching([$request->permission_id]);
            $actionPrefix = 'Asignó';
        } else {
            $role->permissions()->detach($request->permission_id);
            $actionPrefix = 'Removió';
        }

        // Audit Log
        auth()->user()->auditLog(
            "{$actionPrefix} el permiso '{$permission->name}' al rol legado '{$role->nombre}'",
            'Seguridad/Roles',
            $role->id_rol
        );

        // Clear cache for all users of this role in this company
        $users = Usuario::where('id_rol', $role->id_rol)
            ->whereHas('empresa', function($q) use ($id_empresa) {
                $q->where('empresa.id_empresa', $id_empresa);
            })->get();

        foreach($users as $user) {
            $user->clearPermissionCache();
        }

        return response()->json([
            'success' => true,
            'message' => 'Permiso de rol actualizado correctamente.'
        ]);
    }

    public function updateUserPermission(Request $request)
    {
        $request->validate([
            'id_usuario' => 'required|exists:usuario,doc',
            'permission_id' => 'required|exists:permissions,id',
            'active' => 'required'
        ]);

        $id_empresa = session('empresa_id');
        $user = Usuario::findOrFail($request->id_usuario);
        $permission = Permission::findOrFail($request->permission_id);
        
        $rolePermissions = $user->rol ? $user->rol->permissions->pluck('id')->toArray() : [];
        $isInherited = in_array($permission->id, $rolePermissions);

        if ($isInherited == (bool)$request->active) {
            DB::table('user_permissions')
                ->where('user_id', $user->doc)
                ->where('company_id', $id_empresa)
                ->where('permission_id', $permission->id)
                ->delete();
            $message = 'Permiso devuelto al estado heredado del rol.';
        } else {
            DB::table('user_permissions')->updateOrInsert(
                [
                    'user_id' => $user->doc,
                    'company_id' => $id_empresa,
                    'permission_id' => $permission->id,
                ],
                [
                    'active' => (bool)$request->active,
                ]
            );
            $message = (bool)$request->active ? 'Permiso concedido manualmente (excepción).' : 'Permiso denegado manualmente (excepción).';
        }

        $user->clearPermissionCache();

        auth()->user()->auditLog(
            $message . " (Permiso: '{$permission->name}', Usuario: '{$user->nombre_completo}')",
            'Seguridad/Permisos-Individuales',
            $user->doc
        );

        // Recalculate inherited state after the change
        $override = DB::table('user_permissions')
            ->where('user_id', $user->doc)
            ->where('company_id', $id_empresa)
            ->where('permission_id', $permission->id)
            ->first();

        return response()->json([
            'success' => true,
            'message' => $message,
            'inherited' => $override ? false : $isInherited,
        ]);
    }

    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:usuario,doc',
            'role_id' => 'required|exists:rol,id_rol'
        ]);

        $id_empresa = session('empresa_id');
        $user = Usuario::findOrFail($request->user_id);
        $role = Rol::where('id_rol', $request->role_id)->firstOrFail();

        // Assign Legacy id_rol
        $user->update(['id_rol' => $role->id_rol]);

        // Audit Log
        auth()->user()->auditLog(
            "Cambió el rol del usuario '{$user->nombre_completo}' a '{$role->name}'",
            'Seguridad/Asignacion-Rol',
            $user->doc
        );

        Cache::forget("permissions_user_{$user->doc}_company_{$id_empresa}");

        return response()->json([
            'success' => true,
            'message' => 'Rol asignado correctamente.',
            'role_id' => $role->id,
            'role_name' => $role->name,
            'role_permissions' => $role->permissions->pluck('id')
        ]);
    }
}
