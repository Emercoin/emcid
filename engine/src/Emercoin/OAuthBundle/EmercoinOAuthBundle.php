<?php

namespace Emercoin\OAuthBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class EmercoinOAuthBundle extends Bundle
{
    function getParent() {
        return 'FOSOAuthServerBundle';
    }
}
