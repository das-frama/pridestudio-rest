<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Entities\Client;
use App\Http\Controllers\Base\ResourceController;
use App\Http\Responders\ResponderInterface;
use App\Repositories\ClientRepositoryInterface;
use App\Services\ValidationService;

/**
 * Class ClientController
 * @package App\Http\Controllers
 */
class ClientController extends ResourceController
{
    /**
     * ClientController constructor.
     * @param ClientRepositoryInterface $repo
     * @param ResponderInterface $responder
     * @param ValidationService $validator
     */
    public function __construct(
        ClientRepositoryInterface $repo,
        ResponderInterface $responder,
        ValidationService $validator
    ) {
        $this->validation['create'] = [
            'name' => ['required', 'string:0:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string:0:64'],
        ];
        $this->validation['update'] = [
            'name' => ['string:0:255'],
            'email' => ['email'],
            'phone' => ['string:0:64'],
        ];
        parent::__construct(Client::class, $repo, $responder, $validator);
    }
}
