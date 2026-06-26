<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FuelLog;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class GraphqlController extends Controller
{
    public function query(Request $request): JsonResponse
    {
        $query = $request->input('query');

        if (!is_string($query) || trim($query) === '') {
            return response()->json([
                'errors' => [
                    ['message' => 'Query GraphQL wajib diisi.'],
                ],
            ], 400);
        }

        try {
            $result = GraphQL::executeQuery(
                $this->schema(),
                $query,
                null,
                null,
                $request->input('variables', [])
            );

            return response()->json($result->toArray());
        } catch (Throwable $e) {
            return response()->json([
                'errors' => [
                    ['message' => $e->getMessage()],
                ],
            ], 500);
        }
    }

    public function playground(): Response
    {
        return response($this->playgroundHtml())->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function schema(): Schema
    {
        $fuelLogType = new ObjectType([
            'name' => 'FuelLog',
            'fields' => [
                'id'          => Type::int(),
                'vehicle_id'  => Type::int(),
                'driver_name' => Type::string(),
                'liters'      => Type::float(),
                'total_cost'  => Type::float(),
                'fuel_station'=> Type::string(),
                'filled_at'   => Type::string(),
                'created_at'  => Type::string(),
                'updated_at'  => Type::string(),
            ],
        ]);

        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'fuelLogs' => [
                    'type' => Type::listOf($fuelLogType),
                    'resolve' => fn() => FuelLog::all()->toArray(),
                ],
                'fuelLog' => [
                    'type' => $fuelLogType,
                    'args' => [
                        'id' => Type::nonNull(Type::int()),
                    ],
                    'resolve' => fn($root, array $args) => FuelLog::find($args['id']),
                ],
            ],
        ]);

        return new Schema(['query' => $queryType]);
    }

    private function playgroundHtml(): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GraphQL - Fuel Monitoring Service</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/graphiql/1.5.20/graphiql.min.css">
  <style>
    body { margin: 0; height: 100vh; display: flex; flex-direction: column; }
    #auth { display: flex; align-items: center; gap: 12px; padding: 8px 16px; background: #161616; color: #fff; font-family: monospace; }
    #auth input { width: 220px; padding: 7px 10px; border: 1px solid #555; border-radius: 4px; background: #252525; color: #9cff9c; }
    #graphiql { flex: 1; min-height: 0; }
  </style>
</head>
<body>
  <div id="auth">
    <label for="iae-key">X-IAE-KEY</label>
    <input id="iae-key" value="102022400179">
  </div>
  <div id="graphiql"></div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/react/17.0.2/umd/react.production.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/17.0.2/umd/react-dom.production.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/graphiql/1.5.20/graphiql.min.js"></script>
  <script>
    function graphQLFetcher(params) {
      return fetch('/graphql', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-IAE-KEY': document.getElementById('iae-key').value.trim()
        },
        body: JSON.stringify(params)
      }).then(function (response) { return response.json(); });
    }

    ReactDOM.render(
      React.createElement(GraphiQL, {
        fetcher: graphQLFetcher,
        defaultQuery: '# Fuel Monitoring Service\n# Query semua fuel log\n\n{\n  fuelLogs {\n    id\n    vehicle_id\n    driver_name\n    liters\n    total_cost\n    fuel_station\n    filled_at\n  }\n}'
      }),
      document.getElementById('graphiql')
    );
  </script>
</body>
</html>
HTML;
    }
}