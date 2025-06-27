# Changelog

## [1.1.0] - 2025-06-27

### Added
- **Scramble API Documentation** - Replaced L5 Swagger with Scramble for auto-generated API docs
  - Accessible at `/docs/api`
  - Auto-generated from Laravel routes and Form Requests
  - Bearer token authentication configured
- **Session Support** - Added sessions table migration for Laravel session handling
- **Test Helper Script** - Added `test-api.sh` for quick API testing
- **Middleware** - Added `AllowScrambleTesting` middleware for local testing

### Changed
- **Documentation System** - Migrated from Swagger to Scramble
  - Removed all OpenAPI annotations (`@OA`)
  - Removed L5 Swagger package and configuration
  - Updated `composer.json` dependencies
- **Field Names** - Standardized database field names:
  - `berat` → `kg`
  - `diskon` → `discount`
  - `harga_akhir` → `total_harga`
  - `catatan` field removed
- **Validation Messages** - Fixed validation messages to show currency format instead of weight
- **Transaction Controller** - Fixed validation logic and payment method handling

### Fixed
- **Authentication Controller** - Removed duplicate closing comment syntax errors
- **Transaction Factory** - Updated to use new field names
- **Transaction Tests** - Updated all tests to use correct field names
- **Form Requests** - Updated validation rules for new field names
- **Transaction Summary** - Added missing fields for API consistency

### Security
- Configured Laravel Sanctum Bearer token authentication in Scramble
- Added `@unauthenticated` annotations for public endpoints

### Testing
- All 45 tests passing with 262 assertions
- Fixed field name mismatches in test files
- Updated test expectations for new validation messages