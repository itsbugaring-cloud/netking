// ============================================================
// GenieACS Provisioning Script: default.js
// Digunakan oleh GenieACS sebagai "provision" profile untuk
// semua device. Script ini berjalan setiap kali device
// meng-inform ke GenieACS (saat boot atau interval inform).
//
// Cara upload ke GenieACS:
//   PUT http://GENIEACS_IP:7557/provisions/default
//   Content-Type: application/json
//   Body: { "script": "<isi file ini>" }
//
// Atau via UI NBI:
//   http://GENIEACS_IP:3000 → Provisions → New
// ============================================================

//
// ----------------------------------------------------------
// 1. OPTICAL / GPON PARAMETERS
//    GenieACS akan request parameter ini dari CPE setiap inform.
//    Data tersimpan di GenieACS DB dan diambil Laravel via REST API.
// ----------------------------------------------------------
//
declare("InternetGatewayDevice.WANDevice.1.WANGponInterfaceConfig.RXPower",       { value: Date.now() });
declare("InternetGatewayDevice.WANDevice.1.WANGponInterfaceConfig.TXPower",       { value: Date.now() });
declare("InternetGatewayDevice.WANDevice.1.WANGponInterfaceConfig.BiasCurrent",   { value: Date.now() });
declare("InternetGatewayDevice.WANDevice.1.WANGponInterfaceConfig.TransceiverTemperature", { value: Date.now() });
declare("InternetGatewayDevice.WANDevice.1.WANGponInterfaceConfig.SupplyVottage", { value: Date.now() });

// GPON link status
declare("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANGponLinkConfig.GPONLinkStatus", { value: Date.now() });

//
// ----------------------------------------------------------
// 2. DEVICE INFO
// ----------------------------------------------------------
//
declare("InternetGatewayDevice.DeviceInfo.SoftwareVersion",   { value: Date.now() });
declare("InternetGatewayDevice.DeviceInfo.HardwareVersion",   { value: Date.now() });
declare("InternetGatewayDevice.DeviceInfo.Manufacturer",      { value: Date.now() });
declare("InternetGatewayDevice.DeviceInfo.ModelName",         { value: Date.now() });
declare("InternetGatewayDevice.DeviceInfo.ManufacturerOUI",   { value: Date.now() });
declare("InternetGatewayDevice.DeviceInfo.UpTime",            { value: Date.now() });

//
// ----------------------------------------------------------
// 3. WAN / PPPoE
// ----------------------------------------------------------
//
declare("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.ExternalIPAddress", { value: Date.now() });
declare("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.DefaultGateway",    { value: Date.now() });
declare("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.DNSServers",        { value: Date.now() });
declare("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.ConnectionStatus",  { value: Date.now() });
declare("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username",          { value: Date.now() });

// Fallback WAN (WANConnectionDevice.2)
declare("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.ExternalIPAddress", { value: Date.now() });
declare("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.ConnectionStatus",  { value: Date.now() });
declare("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.Username",          { value: Date.now() });

//
// ----------------------------------------------------------
// 4. WiFi 2.4GHz (WLANConfiguration.1)
// ----------------------------------------------------------
//
declare("InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID",              { value: Date.now() });
declare("InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Channel",           { value: Date.now() });
declare("InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.BeaconType",        { value: Date.now() });
declare("InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Enable",            { value: Date.now() });
declare("InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.Standard",          { value: Date.now() });
declare("InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.TotalAssociations", { value: Date.now() });

//
// ----------------------------------------------------------
// 5. WiFi 5GHz (WLANConfiguration.5)
//    Hanya ada pada dual-band ONT; jika tidak ada CPE akan
//    mengembalikan fault yang diabaikan GenieACS secara aman.
// ----------------------------------------------------------
//
declare("InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID",              { value: Date.now() });
declare("InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.Channel",           { value: Date.now() });
declare("InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.BeaconType",        { value: Date.now() });
declare("InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.Enable",            { value: Date.now() });
declare("InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.TotalAssociations", { value: Date.now() });

//
// ----------------------------------------------------------
// 6. LAN
// ----------------------------------------------------------
//
declare("InternetGatewayDevice.LANDevice.1.LANHostConfigManagement.IPInterface.1.IPInterfaceIPAddress",    { value: Date.now() });
declare("InternetGatewayDevice.LANDevice.1.LANHostConfigManagement.IPInterface.1.IPInterfaceSubnetMask",  { value: Date.now() });
declare("InternetGatewayDevice.LANDevice.1.LANHostConfigManagement.DHCPLeaseNumberOfEntries",             { value: Date.now() });

// ============================================================
// END OF PROVISIONING SCRIPT
// ============================================================
