<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\App;
use EDTF\EdtfFactory;
use Illuminate\Database\Eloquent\Model;


class DateInstance extends Model {
 
 protected $table = 'schema2.date_instances';

}

return function (App $app) {
  $app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
  });

  $app->get('/dbtest', function (Request $request, Response $response, $args) {

    if (!!$request->getQueryParams()['range']) {

      $range = $request->getQueryParams()['range'];
      $parser = EdtfFactory::newParser();

      $parsingResultRange = $parser->parse($range);
      if ($parsingResultRange->isValid()) {
        $edtfValueRange = $parsingResultRange->getEdtfValue();

        $all_date_instances = DateInstance::all();

        $params = [$parser, $edtfValueRange];

        $date_instances = array_map(function ($date_instance) use ($params) {
          $event = $date_instance['edtf_date'];
          $parser = $params[0];
          $edtfValueRange = $params[1];
          $parsingResultEvent = $parser->parse($event);
          if ($parsingResultEvent->isValid()) {
            $edtfValueEvent = $parsingResultEvent->getEdtfValue();
            if (is_null($edtfValueEvent) || is_null($edtfValueRange)) {
              $answer = false;
            }
            if ($edtfValueRange->covers($edtfValueEvent) || $edtfValueEvent->covers($edtfValueRange)) {
              return $date_instance['id'];
            }
          }
        }, $all_date_instances->toArray());

        $payload = json_encode($date_instances);

        $response->getBody()->write($payload);
      } else {
        $response->getBody()->write("Invalid input data");
      }
    } else {
      $response->getBody()->write("No date provided");
    };
    
    return $response->withHeader('Content-Type', 'application/json');
  });

  $app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");  
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
        $errorMessage = $parsingResult->getErrorMessage();
        $response->getBody()->write("Invalid date. $errorMessage");
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
};