<?php

namespace ASMBS\SSO;

class RoleResolver
{
    public static function resolve(?string $typeName, ?bool $benefits): string
    {
        if ($typeName === null || $benefits === null) {
            return '';
        }

        if ($benefits === true) {
            return match ($typeName) {
                'Surgeon/Physician Membership',
                'Surgeon/Physician Membership Renewal' => 'active_member',
                'Candidate Member'                     => 'active_member',
                'Corporate Council Representative'     => 'corporate_council_representative',
                'Friend'                               => 'nonmember',
                'Integrated Health',
                'Integrated Health Renewal'            => 'active_member',
                'International',
                'International Renewal'                => 'active_member',
                default                                => 'subscriber',
            };
        }

        return match ($typeName) {
            'Surgeon/Physician Membership',
            'Surgeon/Physician Membership Renewal' => 'active_member',
            'Candidate Member'                     => 'inactive_member',
            'Corporate Council Representative'     => 'corporate_council_representative',
            'Friend'                               => 'nonmember',
            'Integrated Health',
            'Integrated Health Renewal'            => 'inactive_member',
            'International',
            'International Renewal'                => 'inactive_member',
            default                                => 'subscriber',
        };
    }
}
