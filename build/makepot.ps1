# Condei Post Stats - WordPress Plugin POT File Generation Script
# Creates a .pot file for translation using WP-CLI only

param(
	[string]$OutputPath = "languages",
	[string]$PluginName = "abnet-post-stats", 
	[string]$Domain = "abnet-post-stats",
	[switch]$Verbose = $false,
	[switch]$Quiet = $false
)

if ($Quiet -eq $true) {
	$Verbose = $false
}

# Configuration
$CurrentPath = $(Get-Location | Select-Object -ExpandProperty Path)
$PluginPath = if ((Split-Path $CurrentPath -Leaf) -eq "build") {
	Split-Path $CurrentPath -Parent
} else {
	$CurrentPath
}

$PotFileName = "$Domain.pot"

function Write-Log {
	param([string]$Message, [string]$Level = "INFO")
	
	if ($Verbose -or $Level -eq "ERROR" -or $Level -eq "SUCCESS") {
		$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
		$color = switch ($Level) {
			"ERROR" { "Red" }
			"WARNING" { "Yellow" }
			"SUCCESS" { "Green" }
			default { "White" }
		}
		Write-Host "[$timestamp] [$Level] $Message" -ForegroundColor $color
	}
}

function Test-WPCLIAvailable {
	Write-Log "Checking for WP-CLI availability..."
	
	try {
		$wpVersion = & wp --version 2>$null
		if ($wpVersion -match "WP-CLI") {
			Write-Log "Found WP-CLI: $wpVersion" "SUCCESS"
			return $true
		}
	}
	catch {
		# Ignore error
	}
	
	Write-Log "WP-CLI not found or not working" "ERROR"
	Write-Log "Please install WP-CLI from https://wp-cli.org/" "ERROR"
	Write-Log "Or ensure wp.exe/wp.bat is in your PATH" "ERROR"
	return $false
}

function Test-PluginDirectory {
	Write-Log "Checking plugin directory: $PluginPath"
	
	if (-not (Test-Path $PluginPath)) {
		Write-Log "Plugin directory not found: $PluginPath" "ERROR"
		return $false
	}
	
	# Check for main plugin file
	$mainPluginFile = Join-Path $PluginPath "$PluginName.php"
	if (-not (Test-Path $mainPluginFile)) {
		Write-Log "Main plugin file not found: $mainPluginFile" "WARNING"
		Write-Log "Continuing anyway..."
	}
	
	Write-Log "Plugin directory verified" "SUCCESS"
	return $true
}

function New-DirectoryIfNotExists {
	param([string]$Path)
	
	if (-not (Test-Path $Path)) {
		New-Item -ItemType Directory -Path $Path -Force | Out-Null
		Write-Log "Created directory: $Path"
	}
}

function Invoke-WPCLIMakePot {
	param([string]$OutputFile)
	
	Write-Log "Generating POT file using WP-CLI..."
	Write-Log "Source: $PluginPath"
	Write-Log "Output: $OutputFile"
	
	# Build WP-CLI command arguments
	$wpArgs = @(
		"i18n", "make-pot",
		"`"$PluginPath`"",
		"`"$OutputFile`""
	)
	
	# Add optional parameters
	if ($Domain) {
		$wpArgs += "--domain=$Domain"
		$wpArgs += "--slug=$Domain"
	}	

	# Add exclusions for common directories
	$wpArgs += "--exclude=""vendor,node_modules,build,dist,tests,.github,.vscode"""
	
	# Execute WP-CLI command
	try {
		Write-Log "Executing: wp $($wpArgs -join ' ')"
		
		$result = & wp $wpArgs 2>&1
		
		# Check if POT file was actually created, regardless of exit code
		# WP-CLI sometimes returns warnings that cause non-zero exit codes
		if (Test-Path $OutputFile) {
			$fileSize = (Get-Item $OutputFile).Length
			if ($fileSize -gt 0) {
				Write-Log "WP-CLI POT generation completed successfully" "SUCCESS"
				
				# Log any warnings or messages
				if ($result) {
					# Separate warnings from errors
					$resultLines = $result -split "`n" | Where-Object { $_.Trim() -ne "" }
					foreach ($line in $resultLines) {
						if ($line -match "(Warning|Notice|Deprecated)") {
							Write-Log "WP-CLI warning: $line" "WARNING"
						} else {
							Write-Log "WP-CLI output: $line"
						}
					}
				}
				
				if ($LASTEXITCODE -ne 0) {
					Write-Log "Note: WP-CLI returned exit code $LASTEXITCODE but POT file was created successfully" "WARNING"
				}
				
				return $true
			} else {
				Write-Log "POT file was created but is empty" "ERROR"
				return $false
			}
		} else {
			Write-Log "WP-CLI failed with exit code: $LASTEXITCODE" "ERROR"
			if ($result) {
				Write-Log "WP-CLI error: $result" "ERROR"
			}
			return $false
		}
	}
	catch {
		Write-Log "WP-CLI execution failed: $($_.Exception.Message)" "ERROR"
		return $false
	}
}

function Test-PotFile {
	param([string]$PotFile)
	
	Write-Log "Validating generated POT file..."
	
	if (-not (Test-Path $PotFile)) {
		Write-Log "POT file does not exist: $PotFile" "ERROR"
		return $false
	}
	
	try {
		$content = Get-Content $PotFile -Raw -Encoding UTF8
		
		# Check if file is empty
		if (-not $content -or $content.Trim().Length -eq 0) {
			Write-Log "POT file is empty" "ERROR"
			return $false
		}
		
		# Check for required headers
		$requiredHeaders = @(
			"Project-Id-Version:",
			"POT-Creation-Date:",
			"Content-Type:",
			"msgid",
			"msgstr"
		)
		
		foreach ($header in $requiredHeaders) {
			if ($content -notmatch [regex]::Escape($header)) {
				Write-Log "Missing required header: $header" "ERROR"
				return $false
			}
		}
		
		# Count strings
		$msgidMatches = [regex]::Matches($content, "^msgid\s", [System.Text.RegularExpressions.RegexOptions]::Multiline)
		$msgidCount = $msgidMatches.Count
		$fileSize = (Get-Item $PotFile).Length
		
		if ($msgidCount -eq 0) {
			Write-Log "No translatable strings found in POT file" "WARNING"
		}
		
		Write-Log "POT file validation passed" "SUCCESS"
		Write-Log "Found $msgidCount translatable strings ($([math]::Round($fileSize/1KB, 2)) KB)"
		
		return $true
	}
	catch {
		Write-Log "POT file validation failed: $($_.Exception.Message)" "ERROR"
		return $false
	}
}

function Show-Summary {
	param([string]$PotFile, [string]$BuildTime)
	
	if ($Quiet -eq $false) {
		$potInfo = Get-Item $PotFile
		$sizeKB = [math]::Round($potInfo.Length / 1KB, 2)
		
		# Count strings in POT file
		$content = Get-Content $PotFile -Raw -Encoding UTF8
		$stringCount = ([regex]::Matches($content, "^msgid\s", [System.Text.RegularExpressions.RegexOptions]::Multiline)).Count
		
		Write-Host "`n" -NoNewline
		Write-Host "================================================" -ForegroundColor Green
		Write-Host "           POT FILE GENERATION COMPLETE" -ForegroundColor Green
		Write-Host "================================================`n" -ForegroundColor Green
		Write-Host "`tText Domain  : $Domain" -ForegroundColor White
		Write-Host "`tPOT File     : $($potInfo.Name)" -ForegroundColor White
		Write-Host "`tStrings      : $stringCount" -ForegroundColor White
		Write-Host "`tSize         : $sizeKB KB" -ForegroundColor White
		Write-Host "`tLocation     : $($potInfo.Directory)" -ForegroundColor White
		Write-Host "`tBuild Time   : $BuildTime`n" -ForegroundColor White
		Write-Host "================================================" -ForegroundColor Green
		Write-Host "`tPOT FILE IS READY FOR TRANSLATION!" -ForegroundColor Yellow
		Write-Host "================================================" -ForegroundColor Green
		Write-Host "`n`tNext steps:" -ForegroundColor Cyan
		Write-Host "`t1. Create language files (e.g., ro_RO.po) from this POT file" -ForegroundColor Cyan
		Write-Host "`t2. Translate strings using Poedit or similar tools" -ForegroundColor Cyan
		Write-Host "`t3. Compile .po files to .mo files for production use`n" -ForegroundColor Cyan
	}
}

# Main execution
try {
	$startTime = Get-Date
	
	# Resolve output path
	$OutputPath = if ([System.IO.Path]::IsPathRooted($OutputPath)) {
		$OutputPath
	} else {
		Join-Path $PluginPath $OutputPath
	}
	
	if (-not (Test-WPCLIAvailable)) {
		exit 1
	}

	if (-not (Test-PluginDirectory)) {
		exit 1
	}

	# Create output directory
	New-DirectoryIfNotExists $OutputPath
		
	# Generate POT file
	$outputFile = Join-Path $OutputPath $PotFileName
	$success = Invoke-WPCLIMakePot $outputFile
	
	if (-not $success) {
		throw "POT file generation failed"
	}
	
	# Validate POT file
	if (-not (Test-PotFile $outputFile)) {
		throw "POT file validation failed"
	}
	
	# Show summary
	$endTime = Get-Date
	$buildTime = "{0:mm\:ss}" -f ($endTime - $startTime)
	Show-Summary $outputFile $buildTime
	
}
catch {
	Write-Log "POT generation failed: $($_.Exception.Message)" "ERROR"
	exit 1
}
