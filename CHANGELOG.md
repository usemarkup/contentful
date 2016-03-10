CHANGELOG FOR 0.3.0
===================

This version removes the `exclude_archived` option as this was a patch to filter out archived entries that were previously buggily leaking out from the Contentful CDA.

The `IsArchivedFilter` class is removed - using this filter will now break queries.

If any users of this library were not specifying `exclude_archived` as an option set to true, this release does not alter behaviour as this was strictly opt-in.


CHANGELOG FOR 0.2.0
===================

This version moves to using the final (Recommendation) version of [PSR-6](http://www.php-fig.org/psr/psr-6/), the PHP-FIG standard for caches in PHP.  Previous versions made use of the draft version of this PSR, which is not the same as the final version.
