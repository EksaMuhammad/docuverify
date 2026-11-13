const fs = require("fs");
const path = require("path");
const hre = require("hardhat");

async function main() {
  console.log("----------------------------------------------------");
  console.log(`Starting DocumentRegistry deployment on network: ${hre.network.name}`);

  const [deployer] = await hre.ethers.getSigners();
  console.log(`Deployer account: ${deployer.address}`);

  const balance = await hre.ethers.provider.getBalance(deployer.address);
  console.log(`Deployer balance: ${hre.ethers.formatEther(balance)} ETH`);

  const DocumentRegistry = await hre.ethers.getContractFactory("DocumentRegistry");
  const registry = await DocumentRegistry.deploy();
  await registry.waitForDeployment();

  const contractAddress = await registry.getAddress();
  const deploymentTx = registry.deploymentTransaction();
  const receipt = await deploymentTx.wait(1);

  console.log(`\n DocumentRegistry deployed successfully!`);
  console.log(`Contract Address : ${contractAddress}`);
  console.log(`Transaction Hash : ${receipt.hash}`);
  console.log(`Block Number     : ${receipt.blockNumber}`);
  console.log(`Gas Used         : ${receipt.gasUsed.toString()}`);
  console.log("----------------------------------------------------");

  // Save deployment artifact for the Blockchain microservice and Laravel
  const deploymentInfo = {
    network: hre.network.name,
    chainId: (await hre.ethers.provider.getNetwork()).chainId.toString(),
    contractAddress: contractAddress,
    transactionHash: receipt.hash,
    blockNumber: receipt.blockNumber,
    gasUsed: receipt.gasUsed.toString(),
    deployedAt: new Date().toISOString(),
    registrar: deployer.address,
  };

  const outputPath = path.join(__dirname, "../deployment.json");
  fs.writeFileSync(outputPath, JSON.stringify(deploymentInfo, null, 2));
  console.log(`Saved deployment details to: ${outputPath}`);

  return deploymentInfo;
}

main()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error("Deployment failed:", error);
    process.exit(1);
  });

