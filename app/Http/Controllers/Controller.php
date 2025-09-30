<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Return an ActivityPub JSON response
     *
     * @param  mixed  $data
     */
    protected function activityPubResponse($data, int $status = 200, array $headers = []): JsonResponse
    {
        $defaultHeaders = [
            'Content-Type' => 'application/activity+json',
            'X-Content-Type-Options' => 'nosniff',
        ];

        $headers = array_merge($defaultHeaders, $headers);

        return response()->json($data, $status, $headers);
    }

    /**
     * Return an ActivityPub error response
     */
    protected function activityPubError(string $message, int $status = 400): JsonResponse
    {
        return $this->activityPubResponse([
            'error' => $message,
            'code' => $status,
        ], $status);
    }

    /**
     * Return an ActivityPub collection response
     *
     * @param  string  $id  The collection ID
     * @param  int  $totalItems  Total number of items
     * @param  array  $items  The items (for inline collections)
     * @param  array  $extra  Additional properties
     */
    protected function activityPubCollection(
        string $id,
        int $totalItems,
        array $items = [],
        array $extra = []
    ): JsonResponse {
        $collection = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $id,
            'type' => 'OrderedCollection',
            'totalItems' => $totalItems,
        ];

        if (! empty($items)) {
            $collection['orderedItems'] = $items;
        } else {
            $collection['first'] = $id.'?page=1';
        }

        return $this->activityPubResponse(array_merge($collection, $extra));
    }

    /**
     * Return an ActivityPub collection page response
     *
     * @param  string  $id  The page ID
     * @param  string  $partOf  The collection ID
     * @param  int  $totalItems  Total items in collection
     * @param  array  $items  The items on this page
     * @param  int|null  $page  Current page number
     * @param  int  $perPage  Items per page
     */
    protected function activityPubCollectionPage(
        string $id,
        string $partOf,
        int $totalItems,
        array $items,
        ?int $page = 1,
        int $perPage = 20
    ): JsonResponse {
        $response = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $id,
            'type' => 'OrderedCollectionPage',
            'totalItems' => $totalItems,
            'partOf' => $partOf,
            'orderedItems' => $items,
        ];

        // Add pagination links
        if ($page !== null) {
            $hasNext = ($page * $perPage) < $totalItems;
            $hasPrev = $page > 1;

            if ($hasNext) {
                $response['next'] = $partOf.'?page='.($page + 1);
            }

            if ($hasPrev) {
                $response['prev'] = $partOf.'?page='.($page - 1);
            }
        }

        return $this->activityPubResponse($response);
    }

    /**
     * Validate Accept header for ActivityPub
     */
    protected function acceptsActivityPub(\Illuminate\Http\Request $request): bool
    {
        $accept = $request->header('Accept', '');

        $activityPubTypes = [
            'application/activity+json',
            'application/ld+json',
            'application/json',
        ];

        foreach ($activityPubTypes as $type) {
            if (str_contains($accept, $type)) {
                return true;
            }
        }

        return $request->query('format') === 'json' ||
               $request->query('format') === 'activitypub';
    }

    /**
     * Get pagination parameters from request
     *
     * @return array ['page' => int, 'per_page' => int, 'offset' => int]
     */
    protected function getPaginationParams(
        \Illuminate\Http\Request $request,
        int $defaultPerPage = 20,
        int $maxPerPage = 100
    ): array {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(
            $maxPerPage,
            max(1, (int) $request->query('per_page', $defaultPerPage))
        );
        $offset = ($page - 1) * $perPage;

        return [
            'page' => $page,
            'per_page' => $perPage,
            'offset' => $offset,
        ];
    }

    /**
     * Validate that a request is from a local user
     */
    protected function isLocalRequest(\Illuminate\Http\Request $request): bool
    {
        $host = parse_url(config('app.url'), PHP_URL_HOST);
        $requestHost = $request->getHost();

        return $host === $requestHost;
    }

    /**
     * Get the current instance domain
     */
    protected function getInstanceDomain(): string
    {
        return parse_url(config('app.url'), PHP_URL_HOST);
    }
}
