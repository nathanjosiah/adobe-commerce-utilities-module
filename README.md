# Install
1. Run `composer require nathanjosiah/generate-admin-endpoints:1.1.0`

For cloud, that is sufficient. For local environments you also may need to run:
1. Run `bin/magento module:enable Nathanjosiah_GenerateAdminEndpoints`
2. Run `bin/magento setup:upgrade`

# Use
1. Run `bin/magento nathanjosiah:generate-admin-rest-endpoints`

See output like:
```
List of all admin REST endpoints:
GET /V1/store/storeViews
GET /V1/store/storeGroups
GET /V1/store/websites
GET /V1/store/storeConfigs
GET /V1/adobe_io_events/check_configuration
PUT /V1/eventing/updateConfiguration
GET /V1/adobestock/search
<truncated for brevity>
```