$files = Get-ChildItem -Filter "snippet.*.php"

foreach ($file in $files) {
    $newName = $file.Name -replace "^snippet\.", ""
    Copy-Item -Path $file.FullName -Destination $newName
    Remove-Item -Path $file.FullName
} 