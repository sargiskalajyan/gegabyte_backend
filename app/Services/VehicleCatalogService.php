<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class VehicleCatalogService
{
    private const WIKIDATA_ENDPOINT = 'https://query.wikidata.org/sparql';

    /**
     * Fetch Makes (Brands) and Models from Wikidata
     *
     * @return array
     */
    public function getMakesAndModels(): array
    {
        // Updated SPARQL Query
        $sparqlQuery = '
        SELECT ?brandLabel ?modelLabel WHERE {
          ?model wdt:P31 wd:Q3231690.  # Car model
          ?model wdt:P176 ?brand.      # Manufactured by
          SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
        }
        ';

        // Execute HTTP Request
        $response = Http::get(self::WIKIDATA_ENDPOINT, [
            'query' => $sparqlQuery,
            'format' => 'json',
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch data from Wikidata: ' . $response->body());
        }

        $data = $response->json();

       
        // Ensure results['bindings'] is present
        if (!isset($data['results']['bindings'])) {
            throw new \Exception('Unexpected response structure from Wikidata.');
        }

        // Transform response into makes & models array
        $makesAndModels = [];
        foreach ($data['results']['bindings'] as $binding) {
            $make = $binding['brandLabel']['value'];
            $model = $binding['modelLabel']['value'];
            if (!isset($makesAndModels[$make])) {
                $makesAndModels[$make] = [];
            }

            if (!in_array($model, $makesAndModels[$make])) {
                $makesAndModels[$make][] = $model;
            }
        }

        return $makesAndModels;
    }
}