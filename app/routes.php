<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\App;
use EDTF\EdtfFactory;
require '../config/db.php';

function filter_date_instances($all_date_instances, $parser, $edtfValueRange) {
  $params = [$parser, $edtfValueRange];

  $date_instances = array_filter(pg_fetch_all($all_date_instances), function ($date_instance) use ($params) {
    $event = $date_instance['edtf_date'];
    $parser = $params[0];
    $edtfValueRange = $params[1];
    $parsingResultEvent = $parser->parse($event);
    if ($parsingResultEvent->isValid()) {
      $edtfValueEvent = $parsingResultEvent->getEdtfValue();
      if (is_null($edtfValueEvent) || is_null($edtfValueRange)) {
        $answer = false;
      } else {
        if ($edtfValueRange->covers($edtfValueEvent) || $edtfValueEvent->covers($edtfValueRange)) {
          $answer = true;
        } else {
          $answer = false;
        }
      }
    } else {
      $answer = false;
    }
    return $answer;
  });

  return $date_instances;
}

return function (App $app) {
  $app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
  });

  // $app->get('/tombs/groups_priorate_first_and_last_covers', function (Request $request, Response $response, $args) {
  //   if (!!$request->getQueryParams()['range']) {
  //     $range = $request->getQueryParams()['range'];
  //     $parser = EdtfFactory::newParser();

  //     $parsingResultRange = $parser->parse($range);
  //     if ($parsingResultRange->isValid()) {
  //       $edtfValueRange = $parsingResultRange->getEdtfValue();
  //       $db = new db();
  //       $db = $db->connect();

  //       $all_years = pg_query($db, )

  // });

  $app->get('/tombs/date_instance_individuals_covers', function (Request $request, Response $response, $args) {

    if (!!$request->getQueryParams()['range']) {

      $range = $request->getQueryParams()['range'];
      $parser = EdtfFactory::newParser();

      $parsingResultRange = $parser->parse($range);
      if ($parsingResultRange->isValid()) {
        $edtfValueRange = $parsingResultRange->getEdtfValue();
        $db = new db();
        $db = $db->connect();


        $all_date_instances = pg_query($db, "SELECT date_instances.id, date_instance_individuals.id as date_instance_individuals_id, date_instance_individuals.individual_id as individual_id, edtf_date FROM schema2.date_instances inner join schema2.date_instance_individuals on schema2.date_instances.id = schema2.date_instance_individuals.date_instance_id");

        $params = [$parser, $edtfValueRange];

        $date_instances = array_filter(pg_fetch_all($all_date_instances), function ($date_instance) use ($params) {
          $event = $date_instance['edtf_date'];
          $parser = $params[0];
          $edtfValueRange = $params[1];
          $parsingResultEvent = $parser->parse($event);
          if ($parsingResultEvent->isValid()) {
            $edtfValueEvent = $parsingResultEvent->getEdtfValue();
            if (is_null($edtfValueEvent) || is_null($edtfValueRange)) {
              $answer = false;
            } else {
              if ($edtfValueRange->covers($edtfValueEvent) || $edtfValueEvent->covers($edtfValueRange)) {
                $answer = true;
              } else {
                $answer = false;
              }
            }
          } else {
            $answer = false;
          }
          return $answer;
        });

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

  $app->get('/tombs/date_instance_memorials_covers', function (Request $request, Response $response, $args) {

    if (!!$request->getQueryParams()['range']) {

      $range = $request->getQueryParams()['range'];
      $parser = EdtfFactory::newParser();

      $parsingResultRange = $parser->parse($range);
      if ($parsingResultRange->isValid()) {
        $edtfValueRange = $parsingResultRange->getEdtfValue();
        $db = new db();
        $db = $db->connect();


        $all_date_instances = pg_query($db, "SELECT date_instances.id, date_instance_memorials.id as date_instance_memorials_id, date_instance_memorials.memorial_id as memorial_id, edtf_date FROM schema2.date_instances inner join schema2.date_instance_memorials on schema2.date_instances.id = schema2.date_instance_memorials.date_instance_id");

        $date_instances = filter_date_instances($all_date_instances, $parser, $edtfValueRange);

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