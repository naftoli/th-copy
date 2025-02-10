import React, { useState, useEffect } from 'react';

const DonationTable = () => {
  const [donations, setDonations] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Fetch data from the API
    fetch('/api/donations')
      .then(response => response.json())
      .then(data => {
        setDonations(data);
        setLoading(false);
      })
      .catch(error => {
        console.error('Error fetching data:', error);
        setLoading(false);
      });
  }, []);

  const handleEdit = (donationId, subsidyId, newUserId, newAmount) => {
    // Update the subsidy with new user ID and amount
    const updatedDonations = donations.map(donation => {
      if (donation.id === donationId) {
        const updatedSubsidies = donation.subsidies.map(subsidy => {
          if (subsidy.id === subsidyId) {
            return { ...subsidy, userId: newUserId, amount: newAmount };
          }
          return subsidy;
        });
        return { ...donation, subsidies: updatedSubsidies };
      }
      return donation;
    });

    setDonations(updatedDonations);

    // Send update to the server
    fetch(`/api/donations/${donationId}/subsidies/${subsidyId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ userId: newUserId, amount: newAmount }),
    })
      .then(response => response.json())
      .then(data => {
        console.log('Update successful:', data);
      })
      .catch(error => {
        console.error('Error updating data:', error);
      });
  };

  if (loading) {
    return <div>Loading...</div>;
  }

  return (
    <table>
      <thead>
        <tr>
          <th>Donation ID</th>
          <th>Total Amount</th>
          <th>Child User ID</th>
          <th>Amount</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        {donations.map(donation => (
          <React.Fragment key={donation.id}>
            <tr>
              <td>{donation.id}</td>
              <td>{donation.totalAmount}</td>
              <td colSpan="3"></td>
            </tr>
            {donation.subsidies.map(subsidy => (
              <tr key={subsidy.id}>
                <td></td>
                <td></td>
                <td>
                  <input
                    type="text"
                    value={subsidy.userId}
                    onChange={e => handleEdit(donation.id, subsidy.id, e.target.value, subsidy.amount)}
                  />
                </td>
                <td>
                  <input
                    type="number"
                    value={subsidy.amount}
                    onChange={e => handleEdit(donation.id, subsidy.id, subsidy.userId, e.target.value)}
                  />
                </td>
                <td>
                  <button onClick={() => handleEdit(donation.id, subsidy.id, subsidy.userId, subsidy.amount)}>
                    Save
                  </button>
                </td>
              </tr>
            ))}
          </React.Fragment>
        ))}
      </tbody>
    </table>
  );
};

export default DonationTable;
