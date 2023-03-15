<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ResponseGenerator;
use App\Models\Node;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
                        $node->origins()->delete();
                        $node->destinations()->delete();
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
    public function findRoute(Request $request){
        $json = $request->getContent();
        $datos = json_decode($json);

        $validator = Validator::make($request->all(), [
            'origin' => ['required', 'exists:nodes,id'],
            'destination' => ['required','exists:nodes,id'],
        ]);
        if($validator->fails()){
            return ResponseGenerator::generateResponse(400, $validator->errors()->all(), 'Fallos: ');
        }else{

            if($datos->origin == $datos->destination){
                return ResponseGenerator::generateResponse(400, 'The origin can not be destination', 'Something was wrong');
            }else{
                //$origin = Node::find($datos->origin);
                //$destination = Node::find($datos->destination);
                $allRoutes = Node::with('origins','destinations')->get();

                //array_unshift($allRoutes,"");
                //unset($allRoutes[0]);

                //echo($allRoutes);
                //die();
                echo('<pre>');
                print_r($this->getRoute($datos->origin, $datos->destination, $allRoutes, 0));
                echo('</pre>');
            }

        }
    }
    public function getRoute($actualNode, $destNode, $allRoutes, $time, $actualRoute = []){

        $actualArrayNode = $actualNode-1;
        $destArrayNode = $destNode-1;

        $actualRoute[] = [$allRoutes[$actualArrayNode]->name];

        if($allRoutes[$actualArrayNode]->name != $allRoutes[$destArrayNode]->name) {
            $fastestTime = PHP_INT_MAX; // Establecemos un valor alto para la ruta más rápida

            $posiblePaths = $allRoutes[$actualArrayNode]->origins->merge($allRoutes[$actualArrayNode]->destinations);

            if(isset($posiblePaths)){

                foreach($posiblePaths as $path){
                    if(isset($path)){
                        if(!(in_array($path->name, array_column($actualRoute, 0))) && $path->unidirectional == 0){

                            $routeTime = $time + ($path->distance / $path->speed);
                            $actualRoute[] = [$path->name];

                            if($time < $fastestTime) { // Comprobamos si esta ruta es más rápida que la actual
                                $result = $this->getRoute($path->destination, $destNode, $allRoutes, $routeTime, $actualRoute);
                                if ($result['ruta'] && $result['tiempo'] < $fastestTime) {
                                    $fastestTime = $result['tiempo'];
                                    $finalRoute = $result['ruta'];
                                }
                            }
                            array_pop($actualRoute); // Retiramos la ruta actual del array para continuar con la siguiente ruta
                        }
                    if($path->unidirectional == 1){
                        if(!(in_array($path->name, array_column($actualRoute, 0)))){

                            $routeTime = $time + ($path->distance / $path->speed);
                            $actualRoute[] = [$path->name];

                            if($time < $fastestTime) { // Comprobamos si esta ruta es más rápida que la actual
                                $destinationNode = $path->destination-1;
                                $originNode = $path->origin-1;
                                if(!(in_array($allRoutes[$destinationNode]->name, array_column($actualRoute, 0)))){
                                    $result = $this->getRoute($path->destination, $destNode, $allRoutes, $routeTime, $actualRoute);
                                }
                                if(!(in_array($allRoutes[$originNode]->name, array_column($actualRoute, 0)))){
                                    $result = $this->getRoute($path->origin, $destNode, $allRoutes, $routeTime, $actualRoute);
                                }
                                if ($result['ruta'] && $result['tiempo'] < $fastestTime) {
                                    $fastestTime = $result['tiempo'];
                                    $finalRoute = $result['ruta'];
                                }
                            }
                            array_pop($actualRoute); // Retiramos la ruta actual del array para continuar con la siguiente ruta
                        }
                    }
                }
            }
        }else{
            $finalRoute = $actualRoute;
        }
        }else{
            $finalRoute = $actualRoute;
            $fastestTime = $time;
        }
        if(isset($fastestTime) && $fastestTime != PHP_INT_MAX) { // Comprobamos si se ha encontrado una ruta más rápida
            return ['ruta' => $finalRoute, 'tiempo' => number_format($fastestTime, 2)];
        } else {
            return false;
        }
    }
}
