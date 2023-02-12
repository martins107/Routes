<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseGenerator;
use App\Models\Connection;
use App\Models\Node;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConnectionController extends Controller
{
    public function create(Request $request){
        $json = $request->getContent();
        $datos = json_decode($json);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:50'],
            'distance' => ['required', 'integer', 'max_digits: 11'],
            'speed' => ['required', 'integer', 'max_digits: 11'],
            'origin' => ['required', 'integer', 'exists:nodes,id'],
            'destination' => ['required', 'integer', 'exists:nodes,id'],
            'unidirectional' => ['required', 'integer', 'between:0,1'],
        ]);

        if($validator->fails()){
            return ResponseGenerator::generateResponse(400, $validator->errors()->all(), 'Fallos: ');
        }else{
            $connection = new Connection();

            $connection->name = $datos->name;
            $connection->distance = $datos->distance;
            $connection->speed = $datos->speed;
            $connection->origin = $datos->origin;
            $connection->destination = $datos->destination;
            $connection->unidirectional = $datos->unidirectional;

            try{
                $connection->save();
                return ResponseGenerator::generateResponse(200, '', 'Conexión guardada correctamente');
            }catch(\Exception $e){
                return ResponseGenerator::generateResponse(400, $e, 'Fallo al guardar');
            }
        }
    }
    public function update(Request $request){
        $json = $request->getContent();
        $datos = json_decode($json);

        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:connections,id'],
            'name' => ['max:50'],
            'distance' => ['integer', 'max_digits: 11'],
            'speed' => ['integer', 'max_digits: 11'],
            'origin' => ['integer', 'exists:nodes,id'],
            'destination' => ['integer', 'exists:nodes,id'],
            'unidirectional' => ['integer', 'between:0,1'],
        ]);

        if($validator->fails()){
            return ResponseGenerator::generateResponse(400, $validator->errors()->all(), 'Fallos: ');
        }else{
            $connection = Connection::find($datos->id);

            if(isset($datos->name)){
                $connection->name = $datos->name;
            }
            if(isset($datos->distance)){
                $connection->distance = $datos->distance;
            }
            if(isset($datos->speed)){
                $connection->speed = $datos->speed;
            }
            if(isset($datos->origin)){
                $connection->origin = $datos->origin;
            }
            if(isset($datos->destination)){
                $connection->destination = $datos->destination;
            }
            if(isset($datos->unidirectional)){
                $connection->unidirectional = $datos->unidirectional;
            }

            try{
                $connection->save();
                return ResponseGenerator::generateResponse(200, '', 'Conexión actualizada correctamente');
            }catch(\Exception $e){
                return ResponseGenerator::generateResponse(400, $e, 'Fallo al guardar');
            }
        }
    }
    public function delete($id){
        if(isset($id)){
            if(is_numeric($id)){
                $connection = Connection::find($id);
                if($connection){
                    try{
                        $connection->delete();
                        return ResponseGenerator::generateResponse(200, '', 'Conexión borrado correctamente');
                    }catch(\Exception $e){
                        return ResponseGenerator::generateResponse(400, $e, 'Fallo al borrar');
                    }
                }else{
                    return ResponseGenerator::generateResponse(400, '', 'No se ha encontrado el nodo');
                }
            }else{
                return ResponseGenerator::generateResponse(400, '', 'La id debe ser un número');
            }
        }else{
            return ResponseGenerator::generateResponse(400, '', 'No hay id');
        }
    }
    public function list(){
        $nodos = Connection::all();

        return ResponseGenerator::generateResponse(200, $nodos, 'Estos son las conexiones');
    }
}
