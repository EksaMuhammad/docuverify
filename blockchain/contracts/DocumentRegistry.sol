// SPDX-License-Identifier: MIT
pragma solidity ^0.8.28;

/**
 * @title DocumentRegistry
 * @dev Smart contract for registering and verifying digital PDF document fingerprints.
 * Implements access-controlled registration, immutability, and duplicate protection.
 */
contract DocumentRegistry {
    address public owner;
    address public registrar;

    struct DocumentRecord {
        bool isRegistered;
        bytes32 documentHash;
        uint256 registeredAt;
        address registrant;
    }

    // Mapping from documentId (keccak256 of normalized document code) to DocumentRecord
    mapping(bytes32 => DocumentRecord) private _records;

    // Events
    event DocumentRegistered(
        bytes32 indexed documentId,
        bytes32 documentHash,
        uint256 timestamp,
        address indexed registrant
    );

    event RegistrarUpdated(
        address indexed previousRegistrar,
        address indexed newRegistrar
    );

    event OwnershipTransferred(
        address indexed previousOwner,
        address indexed newOwner
    );

    modifier onlyOwner() {
        require(msg.sender == owner, "DocumentRegistry: caller is not the owner");
        _;
    }

    modifier onlyRegistrar() {
        require(
            msg.sender == registrar || msg.sender == owner,
            "DocumentRegistry: caller is not authorized registrar"
        );
        _;
    }

    /**
     * @dev Initialize contract with deployer as owner and default registrar.
     */
    constructor() {
        owner = msg.sender;
        registrar = msg.sender;
        emit OwnershipTransferred(address(0), msg.sender);
        emit RegistrarUpdated(address(0), msg.sender);
    }

    /**
     * @dev Register a new document with its unique ID and SHA-256 fingerprint.
     * @param documentId bytes32 keccak256 hash of normalized document code
     * @param documentHash bytes32 SHA-256 fingerprint of the document byte contents
     */
    function registerDocument(bytes32 documentId, bytes32 documentHash) external onlyRegistrar {
        require(documentId != bytes32(0), "DocumentRegistry: documentId cannot be zero");
        require(documentHash != bytes32(0), "DocumentRegistry: documentHash cannot be zero");
        require(!_records[documentId].isRegistered, "DocumentRegistry: documentId already registered");

        _records[documentId] = DocumentRecord({
            isRegistered: true,
            documentHash: documentHash,
            registeredAt: block.timestamp,
            registrant: msg.sender
        });

        emit DocumentRegistered(documentId, documentHash, block.timestamp, msg.sender);
    }

    /**
     * @dev Read document registration record.
     * @param documentId bytes32 unique document identifier
     * @return isRegistered true if record exists
     * @return documentHash recorded SHA-256 hash
     * @return registeredAt timestamp of registration
     * @return registrant address that performed registration
     */
    function getDocument(bytes32 documentId)
        external
        view
        returns (
            bool isRegistered,
            bytes32 documentHash,
            uint256 registeredAt,
            address registrant
        )
    {
        DocumentRecord memory doc = _records[documentId];
        return (doc.isRegistered, doc.documentHash, doc.registeredAt, doc.registrant);
    }

    /**
     * @dev Direct read-only verification helper.
     * @param documentId bytes32 unique document identifier
     * @param documentHash bytes32 SHA-256 fingerprint to verify
     * @return isValid true if document exists AND hashes match exactly
     * @return isRegistered true if document exists
     * @return registeredHash recorded SHA-256 hash on blockchain
     * @return registeredAt timestamp of registration
     * @return registrant address that registered the document
     */
    function verifyDocument(bytes32 documentId, bytes32 documentHash)
        external
        view
        returns (
            bool isValid,
            bool isRegistered,
            bytes32 registeredHash,
            uint256 registeredAt,
            address registrant
        )
    {
        DocumentRecord memory doc = _records[documentId];
        if (!doc.isRegistered) {
            return (false, false, bytes32(0), 0, address(0));
        }

        bool valid = (doc.documentHash == documentHash);
        return (valid, true, doc.documentHash, doc.registeredAt, doc.registrant);
    }

    /**
     * @dev Update registrar address.
     */
    function setRegistrar(address newRegistrar) external onlyOwner {
        require(newRegistrar != address(0), "DocumentRegistry: new registrar is zero address");
        address old = registrar;
        registrar = newRegistrar;
        emit RegistrarUpdated(old, newRegistrar);
    }

    /**
     * @dev Transfer contract ownership.
     */
    function transferOwnership(address newOwner) external onlyOwner {
        require(newOwner != address(0), "DocumentRegistry: new owner is zero address");
        address old = owner;
        owner = newOwner;
        emit OwnershipTransferred(old, newOwner);
    }
}

