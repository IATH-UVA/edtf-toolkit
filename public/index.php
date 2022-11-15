<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;
use EDTF\EdtfFactory;

require __DIR__ . '/../vendor/autoload.php';

$displayErrorDetails = true;

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);
// $app->setBasePath("/edtf") -- this line is needed for production but commented out for local development;

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
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
  } else {
    $response->getBody()->write("No date provided");
  };
  return $response
      ->withHeader('Access-Control-Allow-Origin', '*')
      ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
      ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->get('/elapsed_years', function (Request $request, Response $response, $args) {
  
  if (!!$request->getQueryParams()['start'] && !!$request->getQueryParams()['end']) {
    $start = $request->getQueryParams()['start'];
    $end = $request->getQueryParams()['end'];
    $parser = EdtfFactory::newParser();
    $parsingResultStart = $parser->parse($start);
    $parsingResultEnd = $parser->parse($end);
    if ($parsingResultStart->isValid() && $parsingResultEnd->isValid()) {
      $edtfValueStart = $parsingResultStart->getEdtfValue();
      $edtfValueEnd = $parsingResultEnd->getEdtfValue();
      $min = $edtfValueStart->getMin();
      $edtfValueEnd = $parsingResultEnd->getEdtfValue();
      $max = $edtfValueEnd->getMax();
      $begin = new DateTime('@' . $min);
      $end = new DateTime('@' . $max);
      $interval = $begin->diff($end);
      $age = $interval->format('%y');
      $response->getBody()->write("$age");
    } else {
      $response->getBody()->write("Invalid date");
    }
  } else {
    $response->getBody()->write("No date provided");
  };
  return $response
      ->withHeader('Access-Control-Allow-Origin', '*')
      ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
      ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    throw new HttpNotFoundException($request);
});

$app->run();
