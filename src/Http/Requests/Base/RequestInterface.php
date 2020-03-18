<?php
declare(strict_types=1);

namespace App\Http\Requests\Base;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface RequestInterface
 * @package App\Http\Requests\Base
 */
interface RequestInterface
{
    /**
     * @return ServerRequestInterface
     */
    public function serverRequest(): ServerRequestInterface;

    /**
     * @return array
     */
    public function required(): array;

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return array|\string[][]
     */
    public function rules(): array;
}
