<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Delivery Challan</title>
<style>
  body {
    font-family: Arial, sans-serif;
    margin: 20px;
    line-height: 1.4;
  }
  h1, h2 {
    margin-bottom: 5px;
  }
  .company-info, .party-info, .shipping-info, .challan-info, .terms, .signatures {
    margin-bottom: 25px;
  }
  .section-title {
    font-weight: bold;
    text-decoration: underline;
    margin-bottom: 10px;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }
  table, th, td {
    border: 1px solid #333;
  }
  th, td {
    padding: 8px;
    text-align: left;
  }
  .signature-block {
    display: inline-block;
    width: 30%;
    vertical-align: top;
    margin-right: 3%;
  }
  .signature-block:last-child {
    margin-right: 0;
  }
  .signature-line {
    margin-top: 40px;
    border-top: 1px solid #000;
    width: 100%;
  }
</style>
</head>
<body>

<div class="company-info">
  <h1>Saavy Agro Fresh Pvt Ltd</h1>
  <p><strong>Address:</strong><br />
    Sr No 78 Pandhari Industrial Estate<br />
    Shivane Pune 411023
  </p>
  <p><strong>Phone No.:</strong> 9823777232<br />
     <strong>Email:</strong> preeti@savvyaqua.com<br />
     <strong>GSTIN:</strong> 27AAJCS2170D1ZI
  </p>
</div>

<div class="challan-info">
  <h2>Delivery Challan For:</h2>
</div>

<div class="party-info">
  <p><strong>Party Name:</strong> Jones Lang LaSalle Property Consultants</p>
  <p><strong>Address:</strong><br />
    Unit 2A-1, 9th Floor, OCTAVE, Salarpuria<br />
    Sattva Knowledge City Phase IV, Sy No. 83/1,<br />
    Raidurg Hyderabad TLG 500081
  </p>
</div>

<div class="shipping-info">
  <p><strong>Shipping Name:</strong> Cognizant Technology Solutions India Private</p>
  <p><strong>Address:</strong><br />
    S. No. 78/1, DLF (DLF) Block 2 SEZ Ground 1st floor, 2nd floor,<br />
    4th & 3rd floor Shivane, DLF cyber city, SEZ, Plot.No:129 to 131<br />
    APHB Colony, Gachbowli Hyderabad TLG 500019
  </p>
  <p><strong>Phone No.:</strong><br />
     <strong>Email:</strong><br />
     <strong>GSTIN:</strong> 36AAACL2089B1ZT
  </p>
</div>

<div class="challan-info">
  <table>
    <tr>
      <th>DC NO</th>
      <td>01</td>
      <th>Date</th>
      <td>20-08-2025</td>
    </tr>
    <tr>
      <th>PO NO</th>
      <td colspan="3">6001910 - OP - 4882700424</td>
    </tr>
  </table>
</div>

<div class="challan-info">
  <table>
    <thead>
      <tr>
        <th>Sl No.</th>
        <th>Item Name</th>
        <th>HSN/SAC Code</th>
        <th>Quantity</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1</td>
        <td>20 Ltr Jars</td>
        <td>22011010</td>
        <td>70</td>
        <td></td>
      </tr>
    </tbody>
  </table>
</div>

<div class="terms">
  <p><strong>Terms and conditions:</strong></p>
  <p>For, Saavy Agro Fresh Pvt Ltd</p>
</div>

<div class="signatures">
  <div class="signature-block">
    <p>Authorised Signature</p>
    <div class="signature-line"></div>
  </div>
  <div class="signature-block">
    <p>Received By</p>
    <p>Name: ____________________</p>
    <p>Comment: ____________________</p>
    <p>Date: ____________________</p>
    <p>Signature: ____________________</p>
  </div>
  <div class="signature-block">
    <p>Delivered By</p>
    <p>Name: ____________________</p>
    <p>Comment: ____________________</p>
    <p>Date: ____________________</p>
    <p>Signature: ____________________</p>
  </div>
</div>

</body>
</html>