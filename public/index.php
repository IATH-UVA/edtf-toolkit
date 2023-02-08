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
        $structured = $humanizer->humanize($edtfValue);
        $simple_humanization = $structured->getSimpleHumanization();
        if(empty($simple_humanization)) {
          $humanized_values = $structured->getStructuredHumanization();
          $context_message = $structured->getContextMessage();
          $first_half = join(", ",$humanized_values);
          $humanized = "$first_half ($context_message)";
        }
        else {
          $humanized = $simple_humanization;
        }
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

$app->get('/check_coverage', function (Request $request, Response $response, $args) {
  if (!!$request->getQueryParams()['event'] && !!$request->getQueryParams()['range']) {
    $event = $request->getQueryParams()['event'];
    $range = $request->getQueryParams()['range'];
    $parser = EdtfFactory::newParser();
    $parsingResultEvent = $parser->parse($event);
    $parsingResultRange = $parser->parse($range);
    if ($parsingResultEvent->isValid() && $parsingResultRange->isValid()) {
      $edtfValueEvent = $parsingResultEvent->getEdtfValue();
      $edtfValueRange = $parsingResultRange->getEdtfValue();
      $coverage = ($edtfValueRange->covers($edtfValueEvent) || $edtfValueEvent->covers($edtfValueRange));
      $stringified = $coverage ? 'true' : 'false';
      
      $response->getBody()->write($stringified);
    } else {
      $response->getBody()->write("Invalid input data");
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
