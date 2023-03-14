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

                print_r($this->getRoute($datos->origin, $datos->destination, $allRoutes, 0));
            }

        }
    }
    public function getRoute($actualNode, $destNode, $allRoutes, $time, $actualRoute = [[],0]){

       //Creamos el booleano de si se ha encontrado la ruta más rápida
        $finalRoute = false;

        $actualArrayNode = $actualNode-1;
        $destArrayNode = $destNode-1;

       //Añadimos el nombre del nodo actual al array de rutas.
        //echo($allRoutes[$actualArrayNode]->name);
        //echo($allRoutes[$actualArrayNode]->name.'-');
        echo($actualArrayNode);
        //die();


        $actualRoute[] = [[$allRoutes[$actualArrayNode]->name],$time];



        if($allRoutes[$actualArrayNode]->name != $allRoutes[$destArrayNode]->name) {
            //Si el nodo actual, es distinto al nodo destino, continuamos buscando rutas

            $posiblePaths = $allRoutes[$actualArrayNode]->origins->merge($allRoutes[$actualArrayNode]->destinations);

            if(isset($posiblePaths)){

                foreach($posiblePaths as $path){
                    //echo($path->name);
                    if(isset($path)){
                        if(!(in_array($path->name, $actualRoute)) && $path->unidirectional == 0){

                            $time += ($path->distance / $path->speed);

                            $actualRoute[] = [[$path->name],$time];
                            //Calculamos el tiempo y agregamos el nombre de esta ruta al array.
                            //echo($path->name);
                            $result = $this->getRoute($path->destination, $destNode, $actualRoute, $time);
                            if (($result['ruta'] && !$finalRoute) || ($result['ruta'] && $result['tiempo'] < $actualRoute[1])) {
                                $finalRoute = $result;
                            }
                        }
                        if($path->unidirectional == 1){

                            if(in_array($allRoutes[$path->destination-1]->name, $actualRoute)){

                                $time += ($path->distance / $path->speed);

                                $actualRoute[] = [[$allRoutes[$path->destination-1]->name],$time];
                                //Calculamos el tiempo y agregamos el nombre de esta ruta al array.

                                $result = $this->getRoute($allRoutes[$path->destination-1],
                                                        $destNode-1, $actualRoute, $time);
                                if (($result['ruta'] && !$finalRoute) || ($result['ruta'] && $result['tiempo'] < $actualRoute[1])) {
                                    $finalRoute = $result;
                                }
                            }
                            if(in_array($allRoutes[$path->origin-1]->name, $actualRoute)){
                                $time += ($path->distance / $path->speed);

                                $actualRoute[] = [[$allRoutes[$path->origin-1]->name],$time];
                                //Calculamos el tiempo y agregamos el nombre de esta ruta al array.

                                $result = $this->getRoute($allRoutes[$path->origin-1],
                                                        $destNode-1, $actualRoute, $time);
                                if (($result['ruta'] && !$finalRoute) || ($result['ruta'] && $result['tiempo'] < $actualRoute[1])) {
                                    $finalRoute = $result;
                                }
                            }
                        }
                    }
                }
            }else{
                $finalRoute = $actualRoute;
            }
        }else{
            $finalRoute = $actualRoute;
        }
    return ['ruta' => $finalRoute, 'tiempo' => $time];

    }
}
/*
                //Creamos el booleano de si se ha encontrado la ruta más rápida
                $finalRoute = false;

                //Añadimos el nombre del nodo actual al array de rutas.

                $actualRoute[] = [[$actualNode->name],$time];

                if($actualNode->id != $destNode->id) {
                    //Si el nodo actual, es distinto al nodo destino, continuamos buscando rutas
                    foreach ($actualNode->origins as $route){
                        //Recorremos todas las conexiones que tienen como origen el nodo actual.
                        if($route->unidirectional == 1 && !(in_array(Node::find($route->destination)->name, $actualRoute))){
                            //Comprobamos si la ruta es unidireccional y si no esta metida en el array de la ruta actual. Si no esta metida llamamos a la función recursiva.
                            $time += ($route->distance / $route->speed);
                            $actualRoute[] = [[$route->name],$time];
                            //Calculamos el tiempo y agregamos el nombre de esta ruta al array.

                            $result = $this->getRoute(Node::find($route->destination), $destNode, $actualRoute, $time);
                            //Llamamos a la función recursiva con los datos actualizados.
                            if (($result['ruta'] && !$finalRoute) || ($result['ruta'] && $result['tiempo'] < $actualRoute[1])) {
                                $finalRoute = $result;
                                //
                            }
                        }
                        if($route->unidirectional == 0 && !(in_array(Node::find($route->origin)->name, $actualRoute))){
                            $time += ($route->distance / $route->speed);
                            $actualRoute[] = [[$route->name],$time];
                            $result = $this->getRoute(Node::find($route->origin), $destNode, $actualRoute, $time);
                            if (($result && !$finalRoute) || ($result && $result['tiempo'] < $actualRoute[1])) {
                                $finalRoute = $result;
                            }
                        }
                        if($route->unidirectional == 0 && !(in_array(Node::find($route->destination)->name, $actualRoute))){
                            $time += ($route->distance / $route->speed);
                            $actualRoute[] = [[$route->name],$time];
                            $result = $this->getRoute(Node::find($route->destination), $destNode, $actualRoute, $time);
                            if (($result && !$finalRoute) || ($result && $result['tiempo'] < $actualRoute[1])) {
                                $finalRoute = $result;
                            }
                        }
                    }
                    foreach ($actualNode->destinations as $route){

                        if($route->unidirectional == 1 && !(in_array(Node::find($route->destination)->name, $actualRoute))){
                            $time += ($route->distance / $route->speed);
                            $actualRoute[] = [[$route->name],$time];
                            $result = $this->getRoute(Node::find($route->destination), $destNode, $actualRoute, $time);
                            if (($result['ruta'] && !$finalRoute) || ($result['ruta'] && $result['tiempo'] < $actualRoute[1])) {
                                $finalRoute = $result;
                            }
                        }
                        if($route->unidirectional == 0 && !(in_array(Node::find($route->origin)->name, $actualRoute))){
                            $time += ($route->distance / $route->speed);
                            $actualRoute[] = [[$route->name],$time];
                            $result = $this->getRoute(Node::find($route->origin), $destNode, $actualRoute, $time);
                            if (($result && !$finalRoute) || ($result && $result['tiempo'] < $actualRoute[1])) {
                                $finalRoute = $result;
                            }
                        }
                        if($route->unidirectional == 0 && !(in_array(Node::find($route->destination)->name, $actualRoute))){
                            $time += ($route->distance / $route->speed);
                            $actualRoute[] = [[$route->name],$time];
                            $result = $this->getRoute(Node::find($route->destination), $destNode, $actualRoute, $time);
                            if (($result && !$finalRoute) || ($result && $result['tiempo'] < $actualRoute[1])) {
                                $finalRoute = $result;
                            }
                        }
                    }
                }
                else{
                    //Si el nodo actual es el mismo que el de destino, igualamos esa ruta a la final.
                $finalRoute = $actualRoute;
                }

                //Retornamos la ruta final y el tiempo que ha tardado.
                return ['ruta' => $finalRoute, 'tiempo' => $time];*/
