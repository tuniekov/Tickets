$files = Get-ChildItem -Filter "chunk.*.tpl"

foreach ($file in $files) {
    $newName = $file.Name -replace "^chunk\.", ""
    Copy-Item -Path $file.FullName -Destination $newName
    Remove-Item -Path $file.FullName
} 