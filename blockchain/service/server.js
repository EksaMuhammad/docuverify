const express = require("express");
const cors = require("cors");
const path = require("path");
const fs = require("fs");
const { ethers } = require("ethers");
require("dotenv").config({ path: path.join(__dirname, "../../.env") });

const app = express();
app.use(cors());
app.use(express.json());

const PORT = parseInt(process.env.PORT_BLOCKCHAIN_SERVICE || "3001", 10);
const RPC_URL = process.env.BLOCKCHAIN_RPC_URL || "http://127.0.0.1:8545";
const NETWORK_NAME = process.env.BLOCKCHAIN_NETWORK || "hardhat_local";

// Fallback test private key for local development (Hardhat Account #0)
// Production or Sepolia keys MUST be provided strictly via environment variable
const PRIVATE_KEY = process.env.REGISTRAR_PRIVATE_KEY || "0xac0974bec39a17e36ba4a6b4d238ff944bacb478cbed5efcae784d7bf4f2ff80";

// Load Contract ABI and Address
const artifactPath = path.join(__dirname, "../artifacts/contracts/DocumentRegistry.sol/DocumentRegistry.json");
const deploymentPath = path.join(__dirname, "../deployment.json");

let contractAbi = [];
if (fs.existsSync(artifactPath)) {
  const artifact = JSON.parse(fs.readFileSync(artifactPath, "utf8"));
  contractAbi = artifact.abi;
}

function getContractAddress() {
  if (process.env.DOCUMENT_REGISTRY_ADDRESS && process.env.DOCUMENT_REGISTRY_ADDRESS.trim() !== "") {
    return process.env.DOCUMENT_REGISTRY_ADDRESS.trim();
  }
  if (fs.existsSync(deploymentPath)) {
    const deployment = JSON.parse(fs.readFileSync(deploymentPath, "utf8"));
    return deployment.contractAddress;
  }
  return null;
}

function getProvider() {
  return new ethers.JsonRpcProvider(RPC_URL);
}

function getSigner(provider) {
  return new ethers.Wallet(PRIVATE_KEY, provider);
}

function getContract(signerOrProvider) {
  const address = getContractAddress();
  if (!address) {
    throw new Error("Contract address is not configured. Deploy contract first or set DOCUMENT_REGISTRY_ADDRESS.");
  }
  return new ethers.Contract(address, contractAbi, signerOrProvider);
}

/**
 * Health check endpoint
 */
app.get("/health", async (req, res) => {
  try {
    const provider = getProvider();
    const contractAddress = getContractAddress();
    let blockNumber = null;
    let connected = false;

    try {
      blockNumber = await provider.getBlockNumber();
      connected = true;
    } catch (e) {
      connected = false;
    }

    res.json({
      status: "ok",
      service: "docuverify-blockchain-service",
      port: PORT,
      network: NETWORK_NAME,
      rpcUrl: RPC_URL,
      contractAddress: contractAddress,
      rpcConnected: connected,
      latestBlock: blockNumber,
      timestamp: new Date().toISOString(),
    });
  } catch (err) {
    res.status(500).json({ status: "error", message: err.message });
  }
});

/**
 * Network status endpoint
 */
app.get("/api/network-status", async (req, res) => {
  const startTime = Date.now();
  try {
    const provider = getProvider();
    const blockNumber = await provider.getBlockNumber();
    const feeData = await provider.getFeeData();
    const network = await provider.getNetwork();
    const latencyMs = Date.now() - startTime;

    res.json({
      success: true,
      chainId: network.chainId.toString(),
      networkName: network.name || NETWORK_NAME,
      blockNumber: blockNumber,
      gasPriceWei: feeData.gasPrice ? feeData.gasPrice.toString() : null,
      gasPriceGwei: feeData.gasPrice ? ethers.formatUnits(feeData.gasPrice, "gwei") : null,
      latencyMs: latencyMs,
      contractAddress: getContractAddress(),
    });
  } catch (err) {
    res.status(503).json({
      success: false,
      status: "TECHNICAL_ERROR",
      message: "Unable to query blockchain network: " + err.message,
    });
  }
});

/**
 * Register document endpoint
 * Body: { documentCode, documentHash }
 */
app.post("/api/documents/register", async (req, res) => {
  const { documentCode, documentHash } = req.body;

  if (!documentCode || typeof documentCode !== "string") {
    return res.status(400).json({ success: false, message: "documentCode is required and must be a string." });
  }
  if (!documentHash || typeof documentHash !== "string") {
    return res.status(400).json({ success: false, message: "documentHash is required and must be a string." });
  }

  // Normalize documentCode: trim + uppercase
  const normalizedCode = documentCode.trim().toUpperCase();
  const documentId = ethers.id(normalizedCode); // keccak256

  // Ensure documentHash is formatted as 32-byte hex
  let formattedHash = documentHash.trim().toLowerCase();
  if (!formattedHash.startsWith("0x")) {
    formattedHash = "0x" + formattedHash;
  }
  if (formattedHash.length !== 66) {
    return res.status(400).json({
      success: false,
      message: "documentHash must be a valid 32-byte hex string (64 characters with or without 0x).",
    });
  }

  try {
    const provider = getProvider();
    const signer = getSigner(provider);
    const contract = getContract(signer);

    // Check if already registered
    const existing = await contract.getDocument(documentId);
    if (existing.isRegistered) {
      return res.status(409).json({
        success: false,
        message: "Document code is already registered on blockchain.",
        documentId: documentId,
        existingHash: existing.documentHash,
        registeredAt: Number(existing.registeredAt),
      });
    }

    // Send transaction
    const tx = await contract.registerDocument(documentId, formattedHash);
    const receipt = await tx.wait(1);

    if (receipt.status !== 1) {
      return res.status(500).json({
        success: false,
        status: "TRANSACTION_FAILED",
        message: "Blockchain transaction reverted.",
        transactionHash: receipt.hash,
      });
    }

    // Get block timestamp
    const block = await provider.getBlock(receipt.blockNumber);
    const timestamp = block ? new Date(block.timestamp * 1000).toISOString() : new Date().toISOString();

    res.json({
      success: true,
      status: "CONFIRMED",
      documentCode: normalizedCode,
      documentId: documentId,
      documentHash: formattedHash,
      transactionHash: receipt.hash,
      blockNumber: receipt.blockNumber,
      gasUsed: receipt.gasUsed.toString(),
      gasPrice: receipt.gasPrice ? receipt.gasPrice.toString() : null,
      network: NETWORK_NAME,
      registrant: signer.address,
      registeredAt: timestamp,
    });
  } catch (err) {
    console.error("Registration error:", err);
    res.status(500).json({
      success: false,
      status: "TECHNICAL_ERROR",
      message: err.message,
      code: err.code || "UNKNOWN_ERROR",
    });
  }
});

/**
 * Verify document endpoint
 * Body: { documentCode, uploadedHash }
 */
app.post("/api/documents/verify", async (req, res) => {
  const { documentCode, uploadedHash } = req.body;

  if (!documentCode || typeof documentCode !== "string") {
    return res.status(400).json({ success: false, message: "documentCode is required." });
  }
  if (!uploadedHash || typeof uploadedHash !== "string") {
    return res.status(400).json({ success: false, message: "uploadedHash is required." });
  }

  const normalizedCode = documentCode.trim().toUpperCase();
  const documentId = ethers.id(normalizedCode);

  let formattedUploadedHash = uploadedHash.trim().toLowerCase();
  if (!formattedUploadedHash.startsWith("0x")) {
    formattedUploadedHash = "0x" + formattedUploadedHash;
  }

  try {
    const provider = getProvider();
    const contract = getContract(provider);

    // Call getDocument directly from blockchain
    const [isRegistered, registeredHash, registeredAt, registrant] = await contract.getDocument(documentId);

    if (!isRegistered) {
      return res.json({
        success: true,
        verificationStatus: "UNVERIFIABLE",
        documentCode: normalizedCode,
        documentId: documentId,
        uploadedHash: formattedUploadedHash,
        blockchainHash: null,
        message: "No record found on blockchain for the given document code.",
      });
    }

    const hashesMatch = (registeredHash.toLowerCase() === formattedUploadedHash);
    const status = hashesMatch ? "VALID" : "MODIFIED";

    res.json({
      success: true,
      verificationStatus: status,
      documentCode: normalizedCode,
      documentId: documentId,
      uploadedHash: formattedUploadedHash,
      blockchainHash: registeredHash,
      registeredAt: new Date(Number(registeredAt) * 1000).toISOString(),
      registrant: registrant,
      message: hashesMatch
        ? "Document is authentic and matches the blockchain record."
        : "Document has been modified. Uploaded hash does not match blockchain record.",
    });
  } catch (err) {
    console.error("Verification RPC error:", err);
    // Explicit technical error reporting per master prompt specification
    res.status(503).json({
      success: false,
      verificationStatus: "TECHNICAL_ERROR",
      documentCode: normalizedCode,
      documentId: documentId,
      uploadedHash: formattedUploadedHash,
      message: "Blockchain communication failure: " + err.message,
      errorDetail: err.code || "RPC_ERROR",
    });
  }
});

/**
 * Read document record by code
 */
app.get("/api/documents/:code", async (req, res) => {
  const code = req.params.code;
  const normalizedCode = code.trim().toUpperCase();
  const documentId = ethers.id(normalizedCode);

  try {
    const provider = getProvider();
    const contract = getContract(provider);

    const [isRegistered, registeredHash, registeredAt, registrant] = await contract.getDocument(documentId);

    if (!isRegistered) {
      return res.status(404).json({
        success: false,
        message: "Document not registered on blockchain.",
        documentCode: normalizedCode,
        documentId: documentId,
      });
    }

    res.json({
      success: true,
      documentCode: normalizedCode,
      documentId: documentId,
      blockchainHash: registeredHash,
      registeredAt: new Date(Number(registeredAt) * 1000).toISOString(),
      registrant: registrant,
    });
  } catch (err) {
    res.status(503).json({
      success: false,
      status: "TECHNICAL_ERROR",
      message: err.message,
    });
  }
});

app.listen(PORT, "127.0.0.1", () => {
  console.log(`DocuVerify Blockchain Service running on http://127.0.0.1:${PORT}`);
});
