@echo off
echo Testing Login API...
echo.

echo Sending POST request to: http://127.0.0.1:8000/api/v1/login
echo.

curl -X POST ^
  http://127.0.0.1:8000/api/v1/login ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"phone\":\"1234567890\",\"otp\":\"123456\"}" ^
  -v

echo.
echo Test completed.
pause
