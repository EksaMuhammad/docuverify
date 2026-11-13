# Smart Contract Specification — `DocumentRegistry.sol`

**Language:** Solidity `^0.8.28`  
**Framework:** Hardhat  
**Testnet Target:** Ethereum Sepolia (`chainId: 11155111`)  
**Local Development:** Hardhat Node (`chainId: 31337`, Port `8545`)

---

## 1. Contract Overview

`DocumentRegistry` is an immutable, access-controlled document anchoring contract. It maintains a mapping from a deterministic `bytes32 documentId` to a `DocumentRecord` structure containing the cryptographic SHA-256 fingerprint, registration timestamp, and registrar address.

```solidity
struct DocumentRecord {
    bool isRegistered;
    bytes32 documentHash;
    uint256 registeredAt;
    address registrant;
}
```

---

## 2. Functions & Interfaces

### 2.1 `registerDocument(bytes32 documentId, bytes32 documentHash)`
- **Access:** Only authorized registrar or contract owner (`onlyRegistrar`).
- **Validation:**
  - `documentId != bytes32(0)`: Reverts with `"DocumentRegistry: documentId cannot be zero"`.
  - `documentHash != bytes32(0)`: Reverts with `"DocumentRegistry: documentHash cannot be zero"`.
  - `!_records[documentId].isRegistered`: Reverts with `"DocumentRegistry: documentId already registered"`.
- **State Effect:** Stores record and sets `isRegistered = true`.
- **Event Emitted:**
  ```solidity
  event DocumentRegistered(
      bytes32 indexed documentId,
      bytes32 documentHash,
      uint256 timestamp,
      address indexed registrant
  );
  ```

### 2.2 `getDocument(bytes32 documentId)`
- **Access:** Public read-only (`view`).
- **Returns:**
  - `isRegistered` (`bool`): True if document exists.
  - `documentHash` (`bytes32`): Recorded SHA-256 hash.
  - `registeredAt` (`uint256`): Unix block timestamp of registration.
  - `registrant` (`address`): Address of registering account.

### 2.3 `verifyDocument(bytes32 documentId, bytes32 documentHash)`
- **Access:** Public read-only (`view`).
- **Returns:**
  - `isValid` (`bool`): True if registered AND provided hash matches recorded hash exactly.
  - `isRegistered` (`bool`): True if document is recorded.
  - `registeredHash` (`bytes32`): Hash stored on blockchain.
  - `registeredAt` (`uint256`): Registration timestamp.
  - `registrant` (`address`): Registrar address.

### 2.4 Administrative Functions
- `setRegistrar(address newRegistrar)`: Owner can update the authorized registrar address. Emits `RegistrarUpdated`.
- `transferOwnership(address newOwner)`: Owner can transfer contract ownership. Emits `OwnershipTransferred`.

---

## 3. Deployment Instructions

### 3.1 Deploying to Local Hardhat Node (Port 8545)
```bash
# 1. Start Hardhat Node
cd blockchain && npx hardhat node --port 8545

# 2. Deploy Contract
cd blockchain && npx hardhat run scripts/deploy.js --network localhost
```

### 3.2 Deploying to Ethereum Sepolia Testnet
Configure `.env`:
```env
SEPOLIA_RPC_URL=https://sepolia.infura.io/v3/YOUR_INFURA_KEY
REGISTRAR_PRIVATE_KEY=0xYOUR_TESTNET_PRIVATE_KEY
```
Execute deployment:
```bash
cd blockchain && npx hardhat run scripts/deploy.js --network sepolia
```
The script will output the deployed contract address and update `blockchain/deployment.json`.
