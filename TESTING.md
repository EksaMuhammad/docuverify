# Testing Guide & Academic Evaluation Suite — DocuVerify

---

## 1. Overview of Automated Test Suites

DocuVerify includes two automated test suites:
1. **Smart Contract Unit Tests (Hardhat / Mocha / Chai):** Validates EVM state transitions, access control, duplicate prevention, and zero-value protections.
2. **Laravel Feature & Unit Tests (PHPUnit):** Validates SHA-256 computation, Keccak-256 derivation, PDF header magic byte verification, authentication, public verification 4-state logic, and SUS calculation.

---

## 2. Running Smart Contract Tests

Navigate to `blockchain/` and execute Hardhat tests:
```bash
cd blockchain
HOME=/tmp npx hardhat test
```

### Verified Test Matrix:
- `Initialization & Access Control`:
  - Deployer is set as owner.
  - Owner can update registrar.
  - Non-owner cannot update registrar.
- `Document Registration`:
  - Registers valid document and emits `DocumentRegistered` event.
  - Rejects duplicate registration of the same `documentId`.
  - Rejects unauthorized caller (non-registrar).
  - Rejects zero `documentId` and zero `documentHash`.
- `Verification View Function`:
  - Returns `VALID` (`isValid = true`) when hash matches exactly.
  - Returns `MODIFIED` (`isValid = false`, `isRegistered = true`) when hash differs.
  - Returns `UNVERIFIABLE` (`isRegistered = false`) when `documentId` does not exist.

---

## 3. Running Laravel Application Tests

From the root directory `/Users/admin/docuverify`:
```bash
php artisan test
```

### Verified Feature & Unit Tests:
- `DocumentHashServiceTest` (7 tests):
  - `test_compute_sha256_computes_accurate_hex_hash`
  - `test_normalize_code_trims_and_uppercases`
  - `test_derive_document_id_returns_exact_keccak256_bytes32` (verifies parity with `ethers.id`)
  - `test_generate_document_code_format`
  - `test_validate_pdf_accepts_valid_pdf_header`
  - `test_validate_pdf_rejects_non_pdf_extension`
  - `test_validate_pdf_rejects_invalid_magic_bytes`
- `AuthTest` (5 tests):
  - Login screen render, login validation, bad password rejection, route middleware protection, logout.
- `PublicVerificationTest` (5 tests):
  - Public verify view accessible without authentication.
  - `VALID` status returned when uploaded hash matches blockchain record.
  - `MODIFIED` status returned when file bytes are altered.
  - `UNVERIFIABLE` status returned when document code is not registered.
  - `TECHNICAL_ERROR` returned on RPC network failure (verifying requirement: never misclassified as unverifiable).
- `AdminDocumentWorkflowTest` (3 tests):
  - Registration screen access, database record creation, temporary file destruction, search/filtering.
- `ResearchDashboardTest` (3 tests):
  - Research dashboard rendering, SUS score calculation formula (100.0 on positive scale, Bangor Grade A), CSV export streaming.

---

## 4. Academic Evaluation Scenarios for Skripsi / Research

To perform experimental evaluation for thesis research:

| Scenario # | Test Description | Action | Expected Outcome |
| :---: | :--- | :--- | :---: |
| **S1** | **Original Authentic PDF** | Upload identical unmodified file with registered code | `VALID` (Green) |
| **S2** | **Text Alteration** | Edit any text character inside the PDF | `MODIFIED` (Red) |
| **S3** | **Image Alteration** | Replace or alter an image/signature | `MODIFIED` (Red) |
| **S4** | **Page Removal** | Delete one page from the PDF | `MODIFIED` (Red) |
| **S5** | **Page Addition** | Append an extra blank page to the PDF | `MODIFIED` (Red) |
| **S6** | **Re-saving without Visual Change** | Save PDF with another PDF tool (updates metadata bytes) | `MODIFIED` (Red) |
| **S7** | **Unregistered Document** | Submit unknown document code or unanchored PDF | `UNVERIFIABLE` (Amber) |
| **S8** | **RPC Node Network Outage** | Disable or stop blockchain service | `TECHNICAL ERROR` (Gray) |

All verification attempts are tracked in `verification_logs` with exact processing time in milliseconds and can be exported directly via the **Academic Research & Evaluation Panel** (`/admin/research`) to CSV.
