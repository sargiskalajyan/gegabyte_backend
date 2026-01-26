<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;

class VehicleCatalogService
{
    private const WIKIDATA_ENDPOINT = 'https://query.wikidata.org/sparql';

    /**
     * Get makes and models, preferring auto.am scraping with a Wikidata fallback.
     *
     * @return array
     */
    public function getMakesAndModels(): array
    {
        try {
            $fromAuto = $this->getMakesAndModelsFromAutoAm();
            if (!empty($fromAuto)) {
                return $fromAuto;
            }
        } catch (\Throwable $e) {
            return array();
        }

        return array();
//        return $this->getMakesAndModelsFromWikidata();
    }

    /**
     * Scrape auto.am to collect make names and try to fetch models per make.
     * This implementation is tolerant: it parses the main page for the
     * `select#filter-make` options and then tries several likely AJAX
     * endpoints to request models. If none succeed, models will be empty arrays.
     *
     * @return array<string, string[]>
     */
    public function getMakesAndModelsFromAutoAm(int $autoCategoryId = null): array
    {
        $base = 'https://auto.am';
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (compatible; VehicleCatalogService/1.0)'
        ];

        $url = $base;
        if ($autoCategoryId !== null) {
            $url = rtrim($base, '/') . '/filters/' . intval($autoCategoryId);
        }

        $resp = Http::withHeaders($headers)->get($url);
        if (!$resp->successful()) {
            return [];
        }

        $html = $resp->body();


        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $makes = [];
        $options = $xpath->query('//select[@id="filter-make"]/option');

        if ($options && $options->length) {
            foreach ($options as $opt) {
                $value = trim($opt->getAttribute('value'));
                $name = trim($opt->textContent);
                if ($value === '' || mb_strtolower($name) === 'մակնիշը' || mb_strtolower($name) === 'make') {
                    continue;
                }
                // ignore visual separators like -------------------
                if (preg_match('/^[-\s]{3,}$/', $name)) {
                    continue;
                }
                // prefer numeric ids for AJAX fetching where available
                $makes[$value] = $name;
            }
        }

        $result = [];
        $modelEndpoints = [
            '/sell/models/%s',
        ];

        foreach ($makes as $makeId => $makeName) {
            $models = [];

            foreach ($modelEndpoints as $endpoint) {
                $paramsList = [
                    ['make' => $makeId],
                    ['make_id' => $makeId],
                    ['makeId' => $makeId],
                    ['id' => $makeId],
                    ['make[]' => $makeId],
                ];

                if (strpos($endpoint, '%s') !== false) {
                    $url = rtrim($base, '/') . '/' . ltrim(sprintf($endpoint, $makeId), '/');
                    $paramAttempts = [[]];
                } else {
                    $url = rtrim($base, '/') . '/' . ltrim($endpoint, '/');
                    $paramAttempts = $paramsList;
                }

                foreach ($paramAttempts as $params) {
                    try {
                        $r = Http::withHeaders($headers)->get($url, $params);
                    } catch (\Throwable $e) {
                        continue;
                    }

                    if (!$r->successful()) {
                        continue;
                    }

                    $body = $r->body();

                    if (strpos($url, '/sell/models') !== false) {
                        libxml_use_internal_errors(true);
                        $domSell = new \DOMDocument();
                        if (@$domSell->loadHTML($body)) {
                            $xpSell = new \DOMXPath($domSell);
                            $sellOpts = $xpSell->query('//select[@id="v-model"]/option');
                            if ($sellOpts && $sellOpts->length) {
                                foreach ($sellOpts as $so) {
                                    $txt = trim($so->textContent);
                                    if ($txt === '' || mb_strtolower($txt) === 'model' || mb_strtolower($txt) === 'մոդելը') {
                                        continue;
                                    }
                                    $models[] = $txt;
                                }
                            }
                        }

                        if (!empty($models)) {
                            break 2;
                        }
                    }

                    $json = json_decode($body, true);
                    if (is_array($json)) {
                        if (array_values($json) === $json) {
                            foreach ($json as $item) {
                                if (is_string($item)) {
                                    $models[] = trim($item);
                                } elseif (is_array($item) && isset($item['name'])) {
                                    $models[] = trim($item['name']);
                                }
                            }
                        } elseif (isset($json['models']) && is_array($json['models'])) {
                            foreach ($json['models'] as $m) {
                                if (is_string($m)) {
                                    $models[] = trim($m);
                                } elseif (is_array($m) && isset($m['name'])) {
                                    $models[] = trim($m['name']);
                                }
                            }
                        }
                    }

                    if (!empty($models)) {
                        break 2;
                    }

                    libxml_use_internal_errors(true);
                    $dom2 = new \DOMDocument();
                    if (@$dom2->loadHTML($body)) {
                        $xpath2 = new \DOMXPath($dom2);
                        $opts = $xpath2->query('//option');
                        if ($opts && $opts->length) {
                            foreach ($opts as $o) {
                                $val = trim($o->getAttribute('value'));
                                $txt = trim($o->textContent);
                                if ($txt === '' || mb_strtolower($txt) === 'model' || mb_strtolower($txt) === 'մոդելը') {
                                    continue;
                                }
                                $models[] = $txt;
                            }
                        }
                    }

                    if (!empty($models)) {
                        break 2;
                    }
                }
            }

            $clean = array_values(array_unique(array_filter($models)));
            $clean = array_values(array_filter($clean, function ($m) {
                $low = mb_strtolower(trim($m));
                if ($low === '' || $low === 'model' || $low === 'մոդելը') {
                    return false;
                }
                if (preg_match('/^[-\s]{3,}$/', $m)) {
                    return false;
                }
                return true;
            }));

            if (!empty($clean)) {
                array_shift($clean);
            }

            $result[$makeName] = $clean;
        }

        return $result;
    }

    /**
     * Original Wikidata-based fetch kept as a fallback.
     *
     * @return array
     */
    private function getMakesAndModelsFromWikidata(): array
    {
        $sparqlQuery = '
        SELECT ?brandLabel ?modelLabel WHERE {
          ?model wdt:P31 wd:Q3231690.  # Car model
          ?model wdt:P176 ?brand.      # Manufactured by
          SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
        }
        ';

        $response = Http::get(self::WIKIDATA_ENDPOINT, [
            'query' => $sparqlQuery,
            'format' => 'json',
        ]);

        if (!$response->successful()) {
            return [];
        }

        $data = $response->json();
        if (!isset($data['results']['bindings'])) {
            return [];
        }

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
