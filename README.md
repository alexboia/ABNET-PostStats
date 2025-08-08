<p align="center">
   <img align="center" width="210" height="200" src="https://github.com/alexboia/ABNET-PostStats/blob/main/logo.png?raw=true" style="margin-bottom: 20px; margin-right: 20px; border-radius: 5px;" />
</p>

<h1 align="center">ABNet Post Stats</h1>

<p align="center">
   A WordPress plugin for displaying statistics about published post counts per month and per year. Initially built for usage on my own website: https://alexboia.net/.
</p>

<p align="center">
   <img align="center" src="https://github.com/alexboia/ABNET-PostStats/blob/main/screenshots/abnet-post-stats.png?raw=true" style="margin-bottom: 20px; margin-right: 20px;" />
</p>

## Features

- Track post publishing statistics per month and per year
- Lightweight and fast performance
- Clean and intuitive interface

## Install

Fetch the archive and download it as you would any WordPress plugin. 
Not currently available on WordPress plug-in directory and will not be.

## Building Installation Kit

### Use `build\package-plugin.ps1`

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `OutputPath` | String | `.\dist` | Directory where the ZIP file will be created |
| `PluginName` | String | `abnet-post-stats` | Name of the plugin (used for folder and file names) |
| `Version` | String | `1.0.0` | Version number to embed in files |
| `IncludeDevFiles` | Switch | `false` | Include development files (examples, tests, etc.) |
| `Verbose` | Switch | `false` | Show detailed logging output |

Call it either from the root plugin directory or from the `build` directory.

### Example

```powershell
.\build\package-plugin.ps1 -Version "1.0.1"
```

Will create an archive in the local `.\dist` folder (within the plugin root).

## Privacy

The plugin does not collect any data. It uses the current WordPress posts table to compute its statistics

## Filter Hooks

The plugin provides several filter hooks to customize its behavior:

#### `abnet_posts_stats_months_count`

Controls the number of months displayed in the monthly statistics dashboard widget.

**Parameters:**
- `$months` _(int)_ - Number of months to display (default: 5)

**Validation:** 

Value is automatically clamped between 1 and 6. If outside this range, defaults to 6.

#### `abnet_posts_stats_years_count`

Controls the number of years displayed in the yearly statistics dashboard widget.

**Parameters:**
- `$years` _(int)_ - Number of years to display (default: `5`)

**Validation:** 

Value is automatically clamped between 1 and 5. If outside this range, defaults to 5.

#### `abnet_posts_stats_max_bar_height`

Controls the maximum height of the chart bars. Expressed in pixels.

**Parameters:**
- `$maxHeight` _(int)_ - The maximum height (default: `200`)
- `$data` _(ABNet_Post_Stats_Result)_ - The current data set being rendered

**Validation:** 

Value is automatically set to `200` if greater than OR equal to `0`.

#### `abnet_posts_stats_show_widget_title`

Controls whether or not the dashboard widget's chart title is displayed.

**Parameters:**
- `$showTitle` _(bool)_ - Whether to display it or not (default: `false`)
- `$data` _(ABNet_Post_Stats_Result)_ - The current data set being rendered

#### `abnet_posts_stats_show_widget_summary`

Controls whether or not the dashboard widget's summary box is displayed.

**Parameters:**
- `$showSummary` _(bool)_ - Whether to display it or not (default: `true`)
- `$data` _(ABNet_Post_Stats_Result)_ - The current data set being rendered

## Next?

I don't really intend to update the code unless there's something really wrong with it or if I need anything else on top of what's already here.
Feel free to fork and use as you please.

## Donate

I put some of my free time into developing and maintaining this plugin.
If helped you in your projects and you are happy with it, you can...

[![ko-fi](https://www.ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/Q5Q01KGLM)