const { expect } = require("chai");
const { ethers } = require("hardhat");

describe("DocumentRegistry Smart Contract", function () {
  let registry;
  let owner;
  let registrar;
  let unauthorized;

  const testDocCode = "DOC-20261113-TEST01";
  const testDocId = ethers.id(testDocCode); // keccak256
  // Example 32-byte hash (SHA-256 of dummy bytes)
  const testDocHash = ethers.sha256(ethers.toUtf8Bytes("%PDF-1.4 sample content"));
  const modifiedDocHash = ethers.sha256(ethers.toUtf8Bytes("%PDF-1.4 modified content"));

  beforeEach(async function () {
    [owner, registrar, unauthorized] = await ethers.getSigners();

    const DocumentRegistry = await ethers.getContractFactory("DocumentRegistry");
    registry = await DocumentRegistry.deploy();
    await registry.waitForDeployment();

    // Set designated registrar
    await registry.setRegistrar(registrar.address);
  });

  describe("Initialization & Access Control", function () {
    it("should set deployer as owner", async function () {
      expect(await registry.owner()).to.equal(owner.address);
    });

    it("should allow owner to update registrar", async function () {
      expect(await registry.registrar()).to.equal(registrar.address);
    });

    it("should prevent non-owner from updating registrar", async function () {
      await expect(
        registry.connect(unauthorized).setRegistrar(unauthorized.address)
      ).to.be.revertedWith("DocumentRegistry: caller is not the owner");
    });
  });

  describe("Document Registration", function () {
    it("should register a valid document and emit DocumentRegistered event", async function () {
      const tx = await registry.connect(registrar).registerDocument(testDocId, testDocHash);
      const receipt = await tx.wait();

      expect(receipt.status).to.equal(1);

      await expect(tx)
        .to.emit(registry, "DocumentRegistered")
        .withArgs(testDocId, testDocHash, (ts) => ts > 0, registrar.address);

      const record = await registry.getDocument(testDocId);
      expect(record.isRegistered).to.be.true;
      expect(record.documentHash).to.equal(testDocHash);
      expect(record.registrant).to.equal(registrar.address);
    });

    it("should reject duplicate registration of the same documentId", async function () {
      await registry.connect(registrar).registerDocument(testDocId, testDocHash);

      await expect(
        registry.connect(registrar).registerDocument(testDocId, modifiedDocHash)
      ).to.be.revertedWith("DocumentRegistry: documentId already registered");
    });

    it("should reject registration from unauthorized caller", async function () {
      await expect(
        registry.connect(unauthorized).registerDocument(testDocId, testDocHash)
      ).to.be.revertedWith("DocumentRegistry: caller is not authorized registrar");
    });

    it("should reject zero documentId or zero documentHash", async function () {
      const zeroBytes = ethers.ZeroHash;

      await expect(
        registry.connect(registrar).registerDocument(zeroBytes, testDocHash)
      ).to.be.revertedWith("DocumentRegistry: documentId cannot be zero");

      await expect(
        registry.connect(registrar).registerDocument(testDocId, zeroBytes)
      ).to.be.revertedWith("DocumentRegistry: documentHash cannot be zero");
    });
  });

  describe("Verification View Function", function () {
    beforeEach(async function () {
      await registry.connect(registrar).registerDocument(testDocId, testDocHash);
    });

    it("should return VALID (isValid = true) when hash matches exactly", async function () {
      const res = await registry.verifyDocument(testDocId, testDocHash);
      expect(res.isValid).to.be.true;
      expect(res.isRegistered).to.be.true;
      expect(res.registeredHash).to.equal(testDocHash);
      expect(res.registrant).to.equal(registrar.address);
    });

    it("should return MODIFIED (isValid = false, isRegistered = true) when hash differs", async function () {
      const res = await registry.verifyDocument(testDocId, modifiedDocHash);
      expect(res.isValid).to.be.false;
      expect(res.isRegistered).to.be.true;
      expect(res.registeredHash).to.equal(testDocHash);
    });

    it("should return UNVERIFIABLE (isRegistered = false) when documentId does not exist", async function () {
      const unregisteredId = ethers.id("DOC-UNKNOWN-9999");
      const res = await registry.verifyDocument(unregisteredId, testDocHash);
      expect(res.isValid).to.be.false;
      expect(res.isRegistered).to.be.false;
    });
  });
});

