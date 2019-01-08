cd base-commander;
yarn install;
yarn build;

Remove-Item ..\mashpia.com\public\new -Recurse -ErrorAction Ignore

mkdir ..\mashpia.com\public\new

Move-Item .\build\* ..\mashpia.com\public\new

Remove-Item build -Recurse -ErrorAction Ignore