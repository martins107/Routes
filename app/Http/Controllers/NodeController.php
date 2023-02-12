<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseGenerator;
use App\Models\Node;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NodeController extends Controller
{
    public function create(Request $request){
        $json = $request->getContent();
        $datos = json_decode($json);

        $node = new Node();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:50'],
        ]);

        if($validator->fails()){
            return ResponseGenerator::generateResponse(400, $validator->errors()->all(), 'Fallos: ');
        }else{
            $node->name = $datos->name;
            try{
                $node->save();
                return ResponseGenerator::generateResponse(200, '', 'Nodo creado correctamente');
            }catch(\Exception $e){
                return ResponseGenerator::generateResponse(400, $e, 'Fallo al guardar');
            }
        }
    }
    public function update(Request $request){
        $json = $request->getContent();
        $datos = json_decode($json);

        $validator = Validator::make($request->all(), [
            'id' => ['required', 'exists:nodes,id'],
            'name' => ['required', 'max:50'],
        ]);

        if($validator->fails()){
            return ResponseGenerator::generateResponse(400, $validator->errors()->all(), 'Fallos: ');
        }else{
            $node = Node::find($datos->id);

            $node->name = $datos->name;
            try{
                $node->save();
                return ResponseGenerator::generateResponse(200, '', 'Nodo actualizado correctamente');
            }catch(\Exception $e){
                return ResponseGenerator::generateResponse(400, $e, 'Fallo al guardar');
            }
        }
    }
    public function delete($id){
        if(isset($id)){
            if(is_numeric($id)){
                $node = Node::find($id);
                if($node){
                    try{
                        $node->delete();
                        return ResponseGenerator::generateResponse(200, '', 'Nodo borrado correctamente');
                    }catch(\Exception $e){
                        return ResponseGenerator::generateResponse(400, $e, 'Fallo al borrar');
                    }
                }else{
                    return ResponseGenerator::generateResponse(400, '', 'no se ha encontrado el nodo');
                }
            }else{
                return ResponseGenerator::generateResponse(400, '', 'La id debe ser un número');
            }
        }else{
            return ResponseGenerator::generateResponse(400, '', 'No hay id');
        }
    }
    public function list(){
        $nodos = Node::all();

        return ResponseGenerator::generateResponse(200, $nodos, 'Estos son los nodos');
    }
}
