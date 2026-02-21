<?php

namespace App\Srd\Contracts;

interface SrdMapperContract
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function map(array $data): array;
}
