<?php

namespace App\Services;

class UrlBuilderService
{
    public static function buildUrl(array $queryParams, string $destinationUrl): string
    {
        $queryParams = array_filter($queryParams);
        $parsedUrl = parse_url($destinationUrl);
        $parsedQuery = [];
        if (isset ($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $parsedQuery);
        }

        $mergedQueryParams = array_merge($parsedQuery, $queryParams);

        $queryString = http_build_query(array_filter($mergedQueryParams));

        $destinationUrl = strtok($destinationUrl, '?') . ($queryString ? '?' . $queryString : '');

        return $destinationUrl;
    }
}
