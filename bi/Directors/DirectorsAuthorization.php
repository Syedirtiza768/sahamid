<?php

namespace SAHamid\BI\Directors;

use SAHamid\BI\Exception\BIException;
use SAHamid\BI\Security\AuthorizationContext;

/** Directors permission is deliberately separate from salesperson analytics. */
class DirectorsAuthorization
{
    private $context;
    private $session;

    public function __construct(AuthorizationContext $context, array $session)
    {
        $this->context = $context;
        $this->session = $session;
    }

    public function assertAccess()
    {
        $permissions = $this->context->getPermissions();
        if (!$this->context->isAdministrator()
            && !in_array('directorreports', $permissions, true)
            && !in_array('directors_dashboard', $permissions, true)) {
            throw new BIException('forbidden', 'Directors reporting permission is required.', 403);
        }
    }

    public function canRead(array $definition)
    {
        $this->assertAccess();
        // Match the ERP script token when registered. Missing mappings fail closed
        // for delegated directors; administrators retain existing BI admin access.
        if ($this->context->isAdministrator()) {
            return true;
        }
        $route = basename($definition['route']);
        $map = isset($this->session['PageSecurityArray']) ? $this->session['PageSecurityArray'] : array();
        return isset($map[$route]) && in_array((int) $map[$route], $this->context->getPageSecurityTokens(), true);
    }
}
