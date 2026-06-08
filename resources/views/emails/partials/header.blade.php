<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Exchange Stuff' }}</title>
</head>
@php
    $logoPath = storage_path('app/public/logo.png');
    $logoSrc = isset($message) && file_exists($logoPath)
        ? $message->embed($logoPath)
        : asset('storage/logo.png');
@endphp
<body style="margin: 0; padding: 0; background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, #ffffff 74%), linear-gradient(to right, #8fc9ec 0%, #b8dff3 42%, #b9dd87 100%); color: #111827; font-family: Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; background: #ffffff; border-bottom: 1px solid #e5e7eb;">
        <tr>
            <td align="center" style="padding: 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 720px; border-collapse: collapse;">
                    <tr>
                        <td>
                            <img src="{{ $logoSrc }}" alt="Exchange Stuff" style="display: block; height: 40px; width: auto; border: 0;">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 720px; border-collapse: collapse;">
                    <tr>
                        <td style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 6px; padding: 32px; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);">
upd