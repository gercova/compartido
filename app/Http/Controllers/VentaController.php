<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illumintate\Support\Facades\DB;
use Carbon\Carbon;
use App\Venta;
use App\DetalleVenta;

class VentaController extends Controller
{
    public function index(Request $request){
        if(!$request->ajax()) return redirect('/');
        $buscar     = $request->buscar;
        $criterio   = $request->criterio;

        if($buscar == ''){
            $ventas = Venta::join('personas as p', 'ventas.idcliente', '=', 'p.id')
            ->join('users as u', 'ventas.idusuario', '=', 'u.id')
            ->select('ventas.id', 'ventas.tipo_comprobante', 'ventas.serie_comprobante', 'ventas.num_comprobante', 'ventas.fecha_hora', 'ventas.impuesto', 'ventas.total', 'ventas.estado', 'p.nombre', 'u.usuario')
            ->orderBy('ventas.id', 'desc')
            ->paginate(5);
        }else{
            $ventas = Venta::join('personas as p', 'ventas.idcliente', '=', 'p.id')
            ->join('users as u', 'ventas.idusuario', '=', 'u.id')
            ->select('ventas.id', 'ventas.tipo_comprobante', 'ventas.serie_comprobante', 'ventas.num_comprobante', 'ventas.fecha_hora', 'ventas.impuesto', 'ventas.total', 'ventas.estado', 'p.nombre', 'u.usuario')
            ->where('ventas.'.$criterio, 'like', '%'.$buscar.'%')
            ->orderBy('ventas.id', 'desc')
            ->paginate(5);
        }
        return [
            'pagination' => [
                'total'         => $ventas->total(),
                'current_page'  => $ventas->currentPage(),
                'per_page'      => $ventas->perPage(),
                'last_page'     => $ventas->lastPage(),
                'from'          => $ventas->firstItem(),
                'to'            => $ventas->lastItem()
            ],
            'ventas' => $ventas
        ];
    }

    public function obtenerCabecera(Request $request){
        if(!$request->ajax()) return redirect('/');
        $id = $request->id;

        $venta = Venta::join('personas as p', 'ventas.idcliente', '=', 'p.id')
        ->join('users as u', 'ventas.idusuario', '=', 'u.id')
        ->select('ventas.id', 'ventas.tipo_comprobante', 'ventas.serie_comprobante', 'ventas.num_comprobante', 'ventas.fecha_hora', 'ventas.impuesto', 'ventas.total', 'ventas.estado', 'p.nombre', 'u.usuario')
        ->where('ventas.id', '=', $id)
        ->orderBy('ventas.id', 'desc')->take(1)->get();
        
        return ['venta' => $venta];
    }

    public function obtenerDetalles(Request $request){
        if(!$request->ajax()) return redirect('/');
        $id = $request->id;

        $detalles = DetalleVenta::join('articulos as a','detalle_ventas.idarticulo','=','a.id')
        ->select('detalle_ventas.cantidad','detalle_ventas.precio', 'detalle_ventas.descuento', 'a.nombre as articulo')
        ->where('detalle_ventas.idventa', '=', $id)
        ->orderBy('detalle_ventas.id', 'desc')->get();
        
        return ['detalles' => $detalles];
    }

    public function store(Request $request){
        if(!$request->ajax()) return redirect('/');
        try{
            DB::beginTransaction();
            $myTime = Carbon::now('America/Lima');

            $venta                  = new Venta();
            $venta->idcliente       = $request->idcliente;
            $venta->idusuario       = \Auth::user()->id;
            $venta->tipo_documento  = $request->tipo_documento;
            $venta->serie_documento = $request->serie_documento;
            $venta->num_documento   = $request->num_documento;
            $venta->fecha_hora      = $myTime->toDateString();
            $venta->impuesto        = $request->impuesto;
            $venta->total           = $request->total;
            $venta->estado          = 'Registrado';
            $venta->save();

            $detalles = $request->data;
            foreach($detalles as $ep => $det){
                $detalle = new DetalleVenta();
                $detalle->idventa       = $venta->id;
                $detalle->idarticulo    = $det['idarticulo'];
                $datalle->cantidad      = $det['cantidad'];
                $detalle->precio        = $det['precio'];
                $detalle->descuento     = $det['descuento'];
                $detalle->save();
            }

            DB::commit();

        }catch(Exception $e){
            DB::rollBack();
        }
    }

    public function desactivar(Request $request){
        if(!$request->ajax()) return redirect('/');
        $venta            = Venta::findOrFail($request->id);
        $venta->estado    = 'Anulado';
        $venta->save();
    }
}