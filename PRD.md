# Product Requirements Document (PRD) — DocuVerify

**Version:** 1.1  
**Date:** 13 November 2026  
**Platform:** Web Application  
**Primary Specification:** `DocuVerify_Master_Prompt_Antigravity_v1.pdf`  
**Reference Document:** `PRD_DocuVerify_v1.pdf`

---

## 1. Executive Summary

DocuVerify is a decentralized digital document verification platform that certifies PDF authenticity and detects tampering. By recording the cryptographic SHA-256 fingerprint of PDF documents onto Ethereum smart contracts, DocuVerify enables any third party to independently verify a document without contacting the issuer or revealing sensitive private contents.

---

## 2. User Roles & Capabilities

### 2.1 Administrator / Certified Registrar
- Authenticate via secure email/password session.
- Register genuine PDF files (upload file, assign document title, generate standard document code).
- Dispatch transactions to the smart contract via the integration layer.
- View real-time transaction confirmation, gas consumed, block number, and hash.
- Audit registered documents, transactions, and user activity.
- Access the Academic Research & Evaluation Panel to monitor performance, accuracy, and SUS scores.

### 2.2 Public Verifier / End-User
- Access public verification page without requiring authentication or account creation.
- Submit a document code (e.g. `DOC-20261113-XXXX`) and upload the PDF file to verify.
- View immediate, audited verification badge:
  - `VALID` (Green)
  - `MODIFIED` (Red)
  - `UNVERIFIABLE` (Amber)
  - `TECHNICAL ERROR` (Gray/Warning)

---

## 3. Functional Requirements

### 3.1 Document Hashing & Temporary Storage
- Only genuine PDF files validated by magic byte header `%PDF-` and maximum 20MB are processed.
- SHA-256 fingerprint is calculated over the raw byte stream.
- Uploaded files are strictly temporary: file pointers are deleted from server storage immediately upon hash extraction, both on success and failure paths.
- Document ID is derived deterministically via Keccak-256 over normalized (trimmed, uppercase UTF-8) document code.

### 3.2 Smart Contract Architecture (`DocumentRegistry`)
- `registerDocument(bytes32 documentId, bytes32 documentHash)`:
  - Restricted to authorized registrar.
  - Rejects duplicate `documentId`.
  - Rejects zero bytes for ID or hash.
  - Records timestamp and registrar address.
  - Emits `DocumentRegistered` event.
- `getDocument(bytes32 documentId)`: Read-only query returning `isRegistered`, `documentHash`, `timestamp`, `registrant`.
- `verifyDocument(bytes32 documentId, bytes32 documentHash)`: View function checking hash equivalence on-chain.

### 3.3 Database Design (MySQL/MariaDB)
- `users`: Administrator credentials and roles.
- `documents`: Document metadata, code, SHA-256 hash, Keccak-256 documentId, status, registration references.
- `transactions`: On-chain transaction receipts, block numbers, gas usage, network indicators.
- `verification_logs`: Audit trail of verification attempts with latency (ms) and IP.
- `activity_logs`: Administrative action logs.

### 3.4 Academic Research & Thesis Evaluation
- Confusion Matrix: Evaluates classification accuracy, precision, recall, and F1-score across test categories.
- Latency Profiling: Computes min, max, and average verification latency in milliseconds.
- Gas & Cost Overhead: Records EVM gas consumed per registration and calculates theoretical fiat equivalent.
- System Usability Scale (SUS): 10 standardized Likert-scale questions computed via Bangor et al. formula.
- One-click CSV export for thesis research publication.

---

## 4. Non-Functional Requirements & Environmental Constraints

- **Port Isolation:** Port `8000` is strictly avoided. Assigned ports:
  - Laravel Web Server: `8001`
  - Vite: `5173`
  - Node.js Blockchain Helper: `3001`
  - Hardhat Local Node: `8545`
  - MariaDB: `3307`
- **Security:** CSRF protection enabled on all web forms; rate limiting on public endpoints; passwords hashed via bcrypt (12 rounds); private keys restricted strictly to backend environment variables.

