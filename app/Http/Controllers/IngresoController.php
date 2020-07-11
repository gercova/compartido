<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illumintate\Support\Facades\DB;
use Carbon\Carbon;
use App\Ingreso;
use App\DetalleIngreso;
use App\User; 

class IngresoController extends Controller{
    
    public function index(Request $request){
        if(!$request->ajax()) return redirect('/');
        $buscar     = $request->buscar;
        $criterio   = $request->criterio;

        if($buscar == ''){
            $ingresos = Ingreso::join('personas as p', 'ingresos.idproveedor', '=', 'p.id')
            ->join('users as u', 'ingresos.idusuario', '=', 'u.id')
            ->select('ingresos.id', 'ingresos.tipo_comprobante', 'ingresos.serie_comprobante', 'ingresos.num_comprobante', 'ingresos.fecha_hora', 'ingresos.impuesto', 'ingresos.total', 'ingresos.estado', 'p.nombre', 'u.usuario')
            ->orderBy('ingresos.id', 'desc')
            ->paginate(5);
        }else{
            $ingresos = Ingreso::join('personas as p', 'ingresos.idproveedor', '=', 'p.id')
            ->join('users as u', 'ingresos.idusuario', '=', 'u.id')
            ->select('ingresos.id', 'ingresos.tipo_comprobante', 'ingresos.serie_comprobante', 'ingresos.num_comprobante', 'ingresos.fecha_hora', 'ingresos.impuesto', 'ingresos.total', 'ingresos.estado', 'p.nombre', 'u.usuario')
            ->where('ingresos.'.$criterio, 'like', '%'.$buscar.'%')
            ->orderBy('ingresos.id', 'desc')
            ->paginate(5);
        }
        return [
            'pagination' => [
                'total'         => $ingresos->total(),
                'current_page'  => $ingresos->currentPage(),
                'per_page'      => $ingresos->perPage(),
                'last_page'     => $ingresos->lastPage(),
                'from'          => $ingresos->firstItem(),
                'to'            => $ingresos->lastItem()
            ],
            'ingresos' => $ingresos
        ];
    }

    public function store(Request $request){
        if(!$request->ajax()) return redirect('/');
        try{
            DB::beginTransaction();
            $myTime = Carbon::now('America/Lima');

            $ingreso                    = new Ingreso();
            $ingreso->idproveedor       = $request->idproveedor;
            $ingreso->idusuario         = \Auth::user()->id;
            $ingreso->tipo_documento    = $request->tipo_documento;
            $ingreso->serie_documento   = $request->serie_documento;
            $ingreso->num_documento     = $request->num_documento;
            $ingreso->fecha_hora        = $myTime->toDateString();
            $ingreso->impuesto          = $request->impuesto;
            $ingreso->total             = $request->total;
            $ingreso->estado            = 'Registrado';
            $ingreso->save();

            $detalles = $request->data;
            foreach($detalles as $ep => $det){
                $detalle = new DetalleIngreso();
                $detalle->idingreso     = $ingreso->id;
                $detalle->idarticulo    = $det['idarticulo'];
                $datalle->cantidad      = $det['cantidad'];
                $detalle->precio        = $det['precio'];
                $detalle->save();
            }

            DB::commit();

        }catch(Exception $e){
            DB::rollBack();
        }
    }

    public function desactivar(Request $request){
        if(!$request->ajax()) return redirect('/');
        $ingreso            = Ingreso::findOrFail($request->id);
        $ingreso->estado    = 'Anulado';
        $ingreso->save();
    }
}