<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="it">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light">
<meta name="supported-color-schemes" content="light">
<style>
@font-face {
  font-family: 'Space Grotesk';
  font-style: normal;
  font-weight: 400;
  font-display: swap;
  src: url(https://fonts.gstatic.com/s/spacegrotesk/v15/V8mDoQDjQSkFtoMM3T6r8E7mPb94C_k3HqUtEw.woff2) format('woff2');
  unicode-range: U+0100-02AF, U+0304, U+0308, U+0329, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
}

@font-face {
  font-family: 'Space Grotesk';
  font-style: normal;
  font-weight: 600;
  font-display: swap;
  src: url(https://fonts.gstatic.com/s/spacegrotesk/v15/V8mDoQDjQSkFtoMM3T6r8E7mPb94C_k3HqUtEw.woff2) format('woff2');
  unicode-range: U+0100-02AF, U+0304, U+0308, U+0329, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
}

@media only screen and (max-width: 600px) {

  .wrapper {
    width: 85% !important;
  }

  .header {
    padding: 30px !important;
  }

  .content-cell {
    padding: 30px !important;
  }

  .inner-body {
    width: 100% !important;
  }

  .footer {
    width: 100% !important;
  }

  .footer .content-cell {
    padding-top: 15px !important;
  }
}
</style>
</head>
<body>
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table class="content text-base" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
<table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
  {{ $header ?? '' }}
<tr>
<td class="content-cell">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
{{ $footer ?? '' }}
</table>
</td>
</tr>
</table>
</body>
</html>