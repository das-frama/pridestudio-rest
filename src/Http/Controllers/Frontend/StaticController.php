<?php
declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Entities\StaticText;
use App\Http\Controllers\Base\AbstractController;
use App\Http\Responders\ResponderInterface;
use App\Repositories\StaticRepositoryInterface;
use App\ResponseFactory;
use Psr\Http\Message\ResponseInterface;

/**
 * Class StaticController
 * @package App\Http\Controllers\Frontend
 */
class StaticController extends AbstractController
{
    protected StaticRepositoryInterface $repo;

    /**
     * StaticController constructor.
     * @param StaticRepositoryInterface $repo
     * @param ResponderInterface $responder
     */
    public function __construct(StaticRepositoryInterface $repo, ResponderInterface $responder)
    {
        parent::__construct($responder);
        $this->repo = $repo;
    }

    /**
     * Get static text: rules.
     * @method GET
     * @return ResponseInterface
     */
    public function rules(): ResponseInterface
    {
        $text = $this->repo->findOne(['key' => 'rules']);
        if (!$text instanceof StaticText) {
            return $this->responder->error(ResponseFactory::NOT_FOUND, 'No rules found.');
        }
        return $this->responder->success($text->text);
    }
}
