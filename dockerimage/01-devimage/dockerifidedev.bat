FOR /F "delims=" %%i IN ('cd') DO set myvar=%%i
rmdir /s /q %myvar%/../../mysqldata
docker create -ti --name ifidedevinict ifidebuildtools bash
REM docker cp ifidedevinict:/usr/local/mysql/data %myvar%/../../mysqldata
REM docker container start ifidedevinict
REM docker exec ifidedevinict rm -rf /usr/local/mysql/data
REM docker stop ifidedevinict


echo Container ifidedevinict créé à transformer en ifidedevimage