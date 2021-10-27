<?php


namespace App\Actions;


use App\Application\Actions\ActionPayload;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Serializer\JsonApiSerializer;
use Psr\Http\Message\ResponseInterface as Response;


abstract class Action
{
    /**
     * @throws Exception
     */
    public function __invoke(): JsonResource
    {
        try {
            return $this->action();
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }

    abstract protected function action(): JsonResource;

    /**
     * @param EloquentBuilder|Builder $query
     * @param array $params
     * @return EloquentBuilder
     */
    protected function injectEagerLoads(EloquentBuilder $query, array $params): EloquentBuilder
    {
        $eagerLoads = $this->getEagerLoads($params);

        return null !== $eagerLoads
            ? $query->with($eagerLoads)
            : $query;
    }

    /**
     * @param array $params
     * @return array|null
     */
    protected function getEagerLoads(array $params): ?array
    {
        $includes = $params['include'] ?? null;

        if (null !== $includes) {
            $includes = collect(explode(',', $params['include']))
                ->filter()
                ->map(function ($name) {
                    return Str::camel(Str::lower($name));
                })->toArray();

            return $this->fractal->parseIncludes($includes)->getRequestedIncludes();
        }
        return null;
    }

    /**
     * @param array|object|null $data
     * @param int $status
     * @param string $contentType
     * @throws JsonException
     * @return Response
     */
    protected function respondWithData(
        $data = null,
        int $status = 200,
        string $contentType = 'application/vnd.api+json'
    ): Response {
        $payload = new ActionPayload($status, $data);

        return $this->respond($payload, $contentType);
    }
}
