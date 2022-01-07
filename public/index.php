<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use EDTF\EdtfFactory;

require __DIR__ . '/../vendor/autoload.php';

$displayErrorDetails = true;

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->get('/humanize', function (Request $request, Response $response, $args) {
  if (!!$request->getQueryParams()['date']){
    $date = $request->getQueryParams()['date'];
    $parser = EdtfFactory::newParser();
    $parsingResult = $parser->parse($date);
    if ($parsingResult->isValid()){
      $edtfValue = $parsingResult->getEdtfValue();
      if(get_class($edtfValue) === "EDTF\Model\Set") {
        $humanizer = EdtfFactory::newStructuredHumanizerForLanguage( 'en' );
        $humanized = $humanizer->humanize($edtfValue)->getSimpleHumanization();
      } else {
        $humanizer = EdtfFactory::newHumanizerForLanguage( 'en' );
        $humanized = $humanizer->humanize($edtfValue);

      }      
      $response->getBody()->write($humanized);
    } else {
      $response->getBody()->write("Invalid date");
    }
    return $response;
  };
});

$app->run();