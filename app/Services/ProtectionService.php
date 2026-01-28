<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class ProtectionService
{
    /**
     * Configuration for protected entities
     */
    protected array $config;

    public function __construct()
    {
        $this->config = config('protected_entities', []);
    }

    /**
     * Check if a user account is protected from deletion
     */
    public function isAccountProtectedFromDeletion(User $user): bool
    {
        $protectedAccounts = $this->config['protected_accounts'] ?? [];

        foreach ($protectedAccounts as $email => $settings) {
            if ($user->email === $email && ($settings['protect_deletion'] ?? false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a user account is protected from role changes
     */
    public function isAccountProtectedFromRoleChange(User $user): bool
    {
        $protectedAccounts = $this->config['protected_accounts'] ?? [];

        foreach ($protectedAccounts as $email => $settings) {
            if ($user->email === $email && ($settings['protect_role_change'] ?? false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a user account is protected from profile updates
     */
    public function isAccountProtectedFromProfileUpdate(User $user): bool
    {
        $protectedAccounts = $this->config['protected_accounts'] ?? [];

        foreach ($protectedAccounts as $email => $settings) {
            if ($user->email === $email && ($settings['protect_profile_update'] ?? false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a role is protected from deletion
     */
    public function isRoleProtectedFromDeletion(Role $role): bool
    {
        $protectedRoles = $this->config['protected_roles'] ?? [];

        return isset($protectedRoles[$role->name]) &&
               ($protectedRoles[$role->name]['protect_deletion'] ?? false);
    }

    /**
     * Check if a role is protected from modification
     */
    public function isRoleProtectedFromModification(Role $role): bool
    {
        $protectedRoles = $this->config['protected_roles'] ?? [];

        return isset($protectedRoles[$role->name]) &&
               ($protectedRoles[$role->name]['protect_modification'] ?? false);
    }

    /**
     * Get protection reason for a user account
     */
    public function getAccountProtectionReason(User $user): ?string
    {
        $protectedAccounts = $this->config['protected_accounts'] ?? [];

        foreach ($protectedAccounts as $email => $settings) {
            if ($user->email === $email) {
                return $settings['reason'] ?? 'Account is protected';
            }
        }

        return null;
    }

    /**
     * Get protection reason for a role
     */
    public function getRoleProtectionReason(Role $role): ?string
    {
        $protectedRoles = $this->config['protected_roles'] ?? [];

        if (isset($protectedRoles[$role->name])) {
            return $protectedRoles[$role->name]['reason'] ?? 'Role is protected';
        }

        return null;
    }

    /**
     * Check if emergency override is enabled
     */
    public function isEmergencyOverrideEnabled(): bool
    {
        return $this->config['emergency_override']['enabled'] ?? false;
    }

    /**
     * Check if current request is allowed emergency override
     */
    public function canEmergencyOverride(): bool
    {
        if (!$this->isEmergencyOverrideEnabled()) {
            return false;
        }

        $allowedIps = $this->config['emergency_override']['allowed_ips'] ?? [];
        if (!empty($allowedIps)) {
            $clientIp = request()->ip();
            return in_array($clientIp, $allowedIps);
        }

        return true; // If no IP restrictions, allow override
    }

    /**
     * Throw protection exception with appropriate message
     */
    public function throwProtectionException(string $message, ?string $reason = null): void
    {
        $fullMessage = $message;
        if ($reason) {
            $fullMessage .= ': ' . $reason;
        }

        if ($this->config['behavior']['log_violations'] ?? true) {
            Log::warning('Protection violation', [
                'message' => $fullMessage,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => auth()->id(),
            ]);
        }

        if ($this->config['behavior']['throw_exceptions'] ?? true) {
            $code = $this->config['behavior']['exception_code'] ?? 403;
            throw new \Exception($fullMessage, $code);
        }
    }

    /**
     * Get all protected accounts
     */
    public function getProtectedAccounts(): array
    {
        return $this->config['protected_accounts'] ?? [];
    }

    /**
     * Get all protected roles
     */
    public function getProtectedRoles(): array
    {
        return $this->config['protected_roles'] ?? [];
    }
}