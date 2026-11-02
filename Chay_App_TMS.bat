@echo off
chcp 65001 >nul
title VIETSUN TMS MOBILE SERVER
echo Đang khởi động ứng dụng Vietsun TMS Mobile...
start "" "http://localhost:8080"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0server.ps1" -Port 8080
pause
