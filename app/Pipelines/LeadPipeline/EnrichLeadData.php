<?php

namespace App\Pipelines\LeadPipeline;

use Closure;

/**
 * Enrich Lead Data Pipeline Stage
 *
 * Enriches lead data with additional information from external sources
 * or derived from existing data. Can be extended for API integrations.
 */
class EnrichLeadData
{
    /**
     * Handle the lead data enrichment
     *
     * @param array<string, mixed> $data
     * @param Closure $next
     * @return mixed
     */
    public function handle(array $data, Closure $next): mixed
    {
        // Skip enrichment if not enabled
        if (!config('crm.pipeline.enable_enrichment', false)) {
            return $next($data);
        }

        // Set default score if not provided
        if (empty($data['score'])) {
            $data['score'] = config('crm.leads.default_score', 'warm');
        }

        // Extract domain from email for company identification
        if (!empty($data['email']) && empty($data['company_name'])) {
            $domain = $this->extractDomain($data['email']);
            if ($domain && !$this->isGenericDomain($domain)) {
                $data['company_name'] = $this->formatCompanyName($domain);
            }
        }

        // Normalize phone number format
        if (!empty($data['phone'])) {
            $data['phone'] = $this->normalizePhoneNumber($data['phone']);
        }

        // Add source if not provided
        if (empty($data['source'])) {
            $data['source'] = 'manual';
        }

        // Add created_by_user_id if authenticated
        if (empty($data['created_by_user_id']) && auth()->check()) {
            $data['created_by_user_id'] = auth()->id();
        }

        return $next($data);
    }

    /**
     * Extract domain from email address
     */
    private function extractDomain(string $email): ?string
    {
        $parts = explode('@', $email);
        return isset($parts[1]) ? strtolower($parts[1]) : null;
    }

    /**
     * Check if domain is a generic email provider
     */
    private function isGenericDomain(string $domain): bool
    {
        $genericDomains = [
            'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com',
            'icloud.com', 'protonmail.com', 'aol.com', 'mail.com'
        ];

        return in_array(strtolower($domain), $genericDomains);
    }

    /**
     * Format domain as company name
     */
    private function formatCompanyName(string $domain): string
    {
        $name = str_replace(['.com', '.org', '.net', '.io'], '', $domain);
        return ucwords(str_replace(['-', '_', '.'], ' ', $name));
    }

    /**
     * Normalize phone number to consistent format
     */
    private function normalizePhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters except + at the start
        $normalized = preg_replace('/[^\d+]/', '', $phone);
        
        // Ensure + is only at the start
        if (str_contains($normalized, '+')) {
            $normalized = '+' . str_replace('+', '', $normalized);
        }

        return $normalized;
    }
}
