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
                'Surgeon/Physician Membership'     => 'active_member',
                'Application'                      => 'nonmember',
                'Candidate Member'                 => 'active_member',
                'Corporate Council Representative' => 'corporate_council_representative',
                'Friend'                           => 'nonmember',
                'Integrated Health'                => 'active_member',
                'International'                    => 'active_member',
                default                            => 'subscriber',
            };
        }

        return match ($typeName) {
            'Surgeon/Physician Membership'     => 'active_member',
            'Application'                      => 'nonmember',
            'Candidate Member'                 => 'inactive_member',
            'Corporate Council Representative' => 'corporate_council_representative',
            'Friend'                           => 'nonmember',
            'Integrated Health'                => 'inactive_member',
            'International'                    => 'inactive_member',
            default                            => 'subscriber',
        };
    }
}