<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Proveedor;
use App\Persona;

class ProveedorController extends Controller{

    public function index(Request $request){
        if(!$request->ajax()) return redirect('/');
        $buscar     = $request->buscar;
        $criterio   = $request->criterio;

        if($buscar == ''){
            $personas = Proveedor::join('personas as p', 'proveedores.id', '=', 'p.id')
            ->select('p.id', 'p.nombre', 'p.tipo_documento', 'p.num_documento', 'p.direccion', 'p.telefono', 'p.email', 'proveedores.contacto', 'proveedores.telefono_contacto')
            ->orderBy('p.id', 'desc')
            ->paginate(5);
        }else{
            $personas = Proveedor::join('personas as p', 'proveedores.id', '=', 'p.id')
            ->select('p.id', 'p.nombre', 'p.tipo_documento', 'p.num_documento', 'p.direccion', 'p.telefono', 'p.email', 'proveedores.contacto', 'proveedores.telefono_contacto')
            ->where('p.'.$criterio, 'like', '%'.$buscar.'%')
            ->orderBy('p.id', 'desc')
            ->paginate(5);
        }
        return [
            'pagination' => [
                'total'         => $personas->total(),
                'current_page'  => $personas->currentPage(),
                'per_page'      => $personas->perPage(),
                'last_page'     => $personas->lastPage(),
                'from'          => $personas->firstItem(),
                'to'            => $personas->lastItem()
            ],
            'personas' => $personas
        ];
    }

    public function store(Request $request){
        if(!$request->ajax()) return redirect('/');
        try{
            DB::beginTransaction();
            $persona                 = new Persona();
            $persona->nombre         = $request->nombre;
            $persona->tipo_documento = $request->tipo_documento;
            $persona->num_documento  = $request->num_documento;
            $persona->direccion      = $request->direccion;
            $persona->telefono       = $request->telefono;
            $persona->email          = $request->email;
            $persona->save();

            $proveedor                    = new Proveedor();
            $proveedor->contacto          = $request->contacto;
            $proveedor->telefono_contacto = $request->telefono_contacto;
            $proveedor->id                = $persona->id;
            $proveedor->save();

            DB::commit();

        }catch(Exception $e){
            DB::rollBack();
        }
    }

    public function update(Request $request){
        if(!$request->ajax()) return redirect('/');
        try{
            DB::beginTransaction();
            //Buscar primero el proveedor a modificar
            $proveedor  = Proveedor::findOrFail($request->id);
            $persona    = Persona::findOrFail($proveedor->id);

            $persona->nombre         = $request->nombre;
            $persona->tipo_documento = $request->tipo_documento;
            $persona->num_documento  = $request->num_documento;
            $persona->direccion      = $request->direccion;
            $persona->telefono       = $request->telefono;
            $persona->email          = $request->email;
            $persona->save();

            $proveedor->contacto          = $request->contacto;
            $proveedor->telefono_contacto = $request->telefono_contacto;
            $proveedor->save();

            DB::commit();

        }catch(Exception $e){
            DB::rollBack();
        }
    }
}
