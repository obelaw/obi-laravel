<?php

namespace Obelaw\Obi\Contracts;

interface Driver
{
    public function prompt(string $userPrompt);
}
