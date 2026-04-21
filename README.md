# ASMBS SSO

A WordPress plugin that handles MemberSuite reverse SSO authentication for ASMBS members and staff.

## How It Works

MemberSuite redirects users to a WordPress page with a `tokenGUID` parameter. The plugin intercepts this request, exchanges the token for user information via the MemberSuite API, looks up or creates the corresponding WordPress account, assigns the appropriate role, and logs the user in.

## Requirements

- WordPress 6.0+
- PHP 8.1+
- Composer
- MemberSuite account with API access

## Installation

Install via Composer from the ASMBS Satis repository:

```bash
composer require asmbs/membersuite-sso
```

## Environment Variables

The following variables must be set in your `.env` file:
MS_EMAIL=your-membersuite-email
MS_PASSWORD=your-membersuite-password
MS_PARTITION_KEY=your-partition-key

## Role Mapping

WordPress roles are assigned based on membership type and benefit status from MemberSuite:

| Membership Type | Benefits | WordPress Role |
|----------------|----------|----------------|
| Surgeon/Physician Membership (+ Renewal) | Yes | active_member |
| Integrated Health (+ Renewal) | Yes | active_member |
| International (+ Renewal) | Yes | active_member |
| Candidate Member | Yes | active_member |
| Surgeon/Physician Membership (+ Renewal) | No | active_member |
| Integrated Health (+ Renewal) | No | inactive_member |
| International (+ Renewal) | No | inactive_member |
| Candidate Member | No | inactive_member |
| Corporate Council Representative | Any | corporate_council_representative |
| Friend | Any | nonmember |
| Application | Any | nonmember |
| Staff (no membership record) | — | preserved |

## User Lookup

When a user logs in the plugin attempts to find their WordPress account in this order:

1. MemberSuite GUID (`mem_guid` user meta)
2. MemberSuite local ID (`mem_key` user meta)
3. Email address
4. Creates a new account if none found

## Logging

SSO activity is logged to `/tmp/sso-debug.log`. Each login attempt logs the user lookup, role assignment, and any meta backfilling.