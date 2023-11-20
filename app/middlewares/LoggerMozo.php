<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class LoggerMozo{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $parametros = $request->getQueryParams();

        $sector = $parametros['rol'];

        if ($sector === 'mozo') {
            $response = $handler->handle($request);
        } else {
            $response = new Response();
            $payload = json_encode(array("mensaje" => "No sos mozo"));
            $response->getBody()->write($payload);
        }

        return $response->withHeader('Content-Type', 'application/json');
    }
}