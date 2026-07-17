@echo off
echo Starting Emergency Response System...

start "" "C:\xampp\xampp-control.exe"

cd backend
start /B php -S localhost:8080 -t ./

cd ..
npm run dev

echo Server running at http://localhost:3000
echo Backend API at http://localhost:8080
pause