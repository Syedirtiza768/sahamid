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

    /**
     * A holder of the directors permission (or an administrator) reads every company-level
     * measure, as in the Sales Executive Report, which is gated on the same permission.
     * User-level scopes are NOT relaxed: bank movement is always limited to the signed-in
     * user's bankaccountusers rows and petty cash to their expense_listing_access tabs.
     * Those filters live in the dataset queries themselves.
     */
    public function canRead(array $definition)
    {
        $this->assertAccess();
        return true;
    }
}
