<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [

        'api/*',
        'apiv2/*',
        'apiv3/*',
        'apiv4/*',
        's_api/*',
		'apiv5/*',
		'apiv6/*',
        'apiv7/*',
        'apiv8/*',
    ];
}
