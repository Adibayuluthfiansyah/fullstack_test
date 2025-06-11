import Link from "next/link";

export default function Home() {
  return (
    <>
    <h1>HOME</h1>
    <br />
    <Link href="/customer">CUSTOMERS PAGE</Link>
    <br />
    <Link href="users">USERS PAGE</Link>
    </>
    
  );
}
