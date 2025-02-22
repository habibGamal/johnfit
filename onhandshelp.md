# Refresh Database Command

To refresh the database and add the basic data, you can use the following Artisan command:

```bash
php artisan app:refresh-db
```

This command will reset the database and populate it with the initial data required for the application.

To create a view for the `X` resource, you can use the following Artisan command:

```bash
php artisan make:filament-page ViewX --resource=XResource --type=ViewRecord
```

To create a relation manager for the `WorkoutPlanResource` and `User` models, you can use the following Artisan command:

```bash
php artisan make:filament-relation-manager WorkoutPlanResource users name
```

`WorkoutPlanResource` is the name of the resource class for the owner (parent) model.  
`users` is the name of the relationship you want to manage.  
`name` is the name of the attribute that will be used to identify users.
