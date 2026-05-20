# Plugin Hooks

This document lists every action and filter hook exposed by the plugin, grouped by the file where each hook is defined.

## abnet-post-stats-plugin-class.php

### `abnet_post_stats_activated`

Type: action

Fires after the plugin activation routine finishes creating and migrating its database tables.

Parameters:

- None.

## includes/class-abnet-poststats-feature.php

### `abnet_poststats_before_feature_setup_{$featureId}`

Type: action

Dynamic hook that fires before a feature directory's PHP files are loaded.

Dynamic portion:

- `$featureId`: The feature directory basename.

Parameters:

- None.

### `abnet_poststats_after_feature_setup_{$featureId}`

Type: action

Dynamic hook that fires after a feature directory's PHP files have been loaded.

Dynamic portion:

- `$featureId`: The feature directory basename.

Parameters:

- None.

## includes/class-abnet-poststats-widget-manager.php

### `abnet_posts_stats_months_count`

Type: filter

Filters the number of months shown in monthly dashboard widgets. This hook is used for both the global monthly widget and content-pillar monthly widgets.

Parameters:

- `$count` (`int`): Number of months to include.
- `$pillar` (`ABNet_PostStats_ContentPillar|null`): Pillar context. `null` for the global widget, or the current content pillar for pillar-specific widgets.

Returns:

- (`int`) The number of months to query and display.

### `abnet_posts_stats_years_count`

Type: filter

Filters the number of years shown in yearly dashboard widgets. This hook is used for both the global yearly widget and content-pillar yearly widgets.

Parameters:

- `$count` (`int`): Number of years to include.
- `$pillar` (`ABNet_PostStats_ContentPillar|null`): Pillar context. `null` for the global widget, or the current content pillar for pillar-specific widgets.

Returns:

- (`int`) The number of years to query and display.

## includes/style-metrics/class-abnet-poststats-style-info-provider.php

### `abnet_posts_stats_style_metric_providers`

Type: filter

Filters the full list of registered style metric provider instances before the provider list is finalized.

Parameters:

- `$providers` (`ABNet_PostStats_StyleMetricProvider[]`): Registered provider instances.
- `$options` (`ABNet_PostStats_StyleMetricOptions`): Current style metric options.

Returns:

- (`ABNet_PostStats_StyleMetricProvider[]`) The provider list to use.

### `abnet_posts_stats_style_metric_enabled_providers`

Type: filter

Filters the provider enabled-state map. This affects both what is computed and what is displayed.

Parameters:

- `$enabledProviders` (`array<string, bool>`): Provider status map keyed by provider key.
- `$providers` (`ABNet_PostStats_StyleMetricProvider[]`): Registered provider instances.
- `$options` (`ABNet_PostStats_StyleMetricOptions`): Current style metric options.

Returns:

- (`array<string, bool>`) The enabled-state map to apply.

## includes/style-metrics/class-abnet-poststats-style-metric-manager.php

### `abnet_poststats_enable_style_metrics_post_types`

Type: filter

Filters the post types that should display the style metrics metabox.

Parameters:

- `$defaultPostTypes` (`string[]`): Default supported post types.

Returns:

- (`string[]`) The post types that should receive the metabox.

### `abnet_poststats_style_metrics_metabox_title`

Type: filter

Filters the style metrics metabox title for a given post type.

Parameters:

- `$defaultTitle` (`string`): Default metabox title.
- `$postType` (`string`): Current post type.

Returns:

- (`string`) The metabox title.

### `abnet_poststats_style_metrics_metabox_context`

Type: filter

Filters the metabox context used for the style metrics metabox.

Parameters:

- `$context` (`string`): Default metabox context.
- `$postType` (`string`): Current post type.

Returns:

- (`string`) The metabox context, typically `normal`, `side`, or `advanced`.

### `abnet_poststats_style_metrics_source_content`

Type: filter

Filters the post content used as the source for style metric computation.

Parameters:

- `$postContent` (`string`): Raw post content that will be analyzed.
- `$post` (`WP_Post`): Current post instance.
- `$options` (`ABNet_PostStats_StyleMetricOptions`): Current style metric options.

Returns:

- (`string`) The content to analyze.

### `abnet_poststats_should_recompute_style_metrics`

Type: filter

Filters whether style metrics should be recomputed when a post is saved.

Parameters:

- `$shouldRecomputeMetrics` (`bool`): Whether recomputation should proceed.
- `$post` (`WP_Post`): Current post instance.
- `$options` (`ABNet_PostStats_StyleMetricOptions`): Current style metric options.

Returns:

- (`bool`) Whether recomputation should run.

### `abnet_poststats_register_style_metric_settings_fields`

Type: action

Fires while the style metrics settings page is registering its fields, allowing third parties to register additional settings fields. As currently implemented, this action fires once per built-in provider during the registration loop.

Parameters:

- `$pageSlug` (`string`): Settings page slug.
- `$settingsGroup` (`string`): Settings API group name.
- `$options` (`ABNet_PostStats_StyleMetricOptions`): Current style metric options.
- `$manager` (`ABNet_PostStats_StyleMetric_Manager`): Current manager instance.

## includes/style-metrics/providers/class-abnet-poststats-negativity-provider.php

### `abnet_posts_stats_default_negative_word_list`

Type: filter

Filters the default negative word list used when no explicit list is configured.

Parameters:

- `$defaultList` (`string[]`): Default negative word list.

Returns:

- (`string[]`) The negative word list to use.

## views/admin-style-metrics-metabox.php

### `abnet_post_stats_style_metrics_bracket_marker_css_class`

Type: filter

Filters the row CSS class used to indicate whether a style metric is within its configured bracket.

Parameters:

- `$defaultBracketMarkerCssClass` (`string`): Default CSS class name.
- `$metric` (`ABNet_PostStats_StyleMetric`): Current metric instance.

Returns:

- (`string`) The CSS class to apply to the row.

### `abnet_post_stats_style_metrics_bracket_description`

Type: filter

Filters the tooltip and description text shown for a metric bracket in the style metrics metabox.

Parameters:

- `$defaultBracketDescription` (`string`): Default bracket description.
- `$metric` (`ABNet_PostStats_StyleMetric`): Current metric instance.

Returns:

- (`string`) The bracket description text.

## views/admin-style-metrics-settings-page.php

### `abnet_posts_stats_before_style_metrics_settings_form`

Type: action

Fires before the style metrics settings form fields are rendered.

Parameters:

- `$options` (`ABNet_PostStats_StyleMetricOptions`): Current style metric options.

### `abnet_posts_stats_before_style_metrics_settings_savebtn`

Type: action

Fires immediately before the style metrics settings submit button is rendered.

Parameters:

- `$options` (`ABNet_PostStats_StyleMetricOptions`): Current style metric options.

### `abnet_posts_stats_after_style_metrics_settings_form`

Type: action

Fires after the full style metrics settings form content has been rendered.

Parameters:

- `$options` (`ABNet_PostStats_StyleMetricOptions`): Current style metric options.

## views/dashboard-widget.php

### `abnet_posts_stats_max_bar_height`

Type: filter

Filters the maximum bar height used when rendering the dashboard widget chart.

Parameters:

- `$maxHeight` (`int`): Maximum bar height in pixels.
- `$data` (`ABNet_PostStats_Result`): Dataset rendered by the widget.

Returns:

- (`int`) The maximum bar height.

### `abnet_posts_stats_show_widget_title`

Type: filter

Filters whether the dashboard widget title should be displayed.

Parameters:

- `$showTitle` (`bool`): Whether to show the title.
- `$data` (`ABNet_PostStats_Result`): Dataset rendered by the widget.

Returns:

- (`bool`) Whether the title should be shown.

### `abnet_posts_stats_show_widget_summary`

Type: filter

Filters whether the dashboard widget summary section should be displayed.

Parameters:

- `$showSummary` (`bool`): Whether to show summary statistics.
- `$data` (`ABNet_PostStats_Result`): Dataset rendered by the widget.

Returns:

- (`bool`) Whether the summary should be shown.

### `abnet_posts_stats_item_bar_height`

Type: filter

Filters the computed bar height for a single chart item.

Parameters:

- `$height` (`float|int`): Computed item bar height in pixels.
- `$item` (`ABNet_PostStats_Item`): Current chart item.
- `$data` (`ABNet_PostStats_Result`): Dataset rendered by the widget.

Returns:

- (`float|int`) The bar height to render.

### `abnet_post_stats_item_bar_color`

Type: filter

Filters the bar color for a single chart item.

Parameters:

- `$color` (`string`): Current bar color.
- `$item` (`ABNet_PostStats_Item`): Current chart item.
- `$data` (`ABNet_PostStats_Result`): Dataset rendered by the widget.

Returns:

- (`string`) The bar color to render.
