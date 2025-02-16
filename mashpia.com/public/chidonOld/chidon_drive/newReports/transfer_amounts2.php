import React, { useState, useEffect } from 'react';
import { PlusCircle, Trash2, Save } from 'lucide-react';

const DonationsManager = () => {
  const [donations, setDonations] = useState([]);
  const [editingSubsidyId, setEditingSubsidyId] = useState(null);
  const [editingValues, setEditingValues] = useState({});

  useEffect(() => {
    // In a real implementation, this would fetch from your API
    // Mock data structure for development
    const mockData = [
      {
        donation_id: 1,
        total_amount: 1000,
        donor_name: "John Doe",
        date: "2024-02-10",
        subsidies: [
          { subsidy_id: 1, user_id: 123, amount: 500 },
          { subsidy_id: 2, user_id: 456, amount: 500 }
        ]
      },
      // Add more mock data as needed
    ];
    setDonations(mockData);
  }, []);

  const handleEditSubsidy = (subsidy) => {
    setEditingSubsidyId(subsidy.subsidy_id);
    setEditingValues({
      user_id: subsidy.user_id,
      amount: subsidy.amount
    });
  };

  const handleSaveSubsidy = async (donationId, subsidyId) => {
    // Validate total amount hasn't been exceeded
    const donation = donations.find(d => d.donation_id === donationId);
    const otherSubsidies = donation.subsidies.filter(s => s.subsidy_id !== subsidyId);
    const totalOtherAmount = otherSubsidies.reduce((sum, s) => sum + s.amount, 0);
    
    if (totalOtherAmount + Number(editingValues.amount) > donation.total_amount) {
      alert('Total subsidies cannot exceed donation amount');
      return;
    }

    // In a real implementation, this would call your API
    const updatedDonations = donations.map(donation => {
      if (donation.donation_id === donationId) {
        return {
          ...donation,
          subsidies: donation.subsidies.map(subsidy => {
            if (subsidy.subsidy_id === subsidyId) {
              return {
                ...subsidy,
                user_id: Number(editingValues.user_id),
                amount: Number(editingValues.amount)
              };
            }
            return subsidy;
          })
        };
      }
      return donation;
    });

    setDonations(updatedDonations);
    setEditingSubsidyId(null);
    setEditingValues({});
  };

  const handleAddSubsidy = (donationId) => {
    const donation = donations.find(d => d.donation_id === donationId);
    const totalSubsidies = donation.subsidies.reduce((sum, s) => sum + s.amount, 0);
    const remainingAmount = donation.total_amount - totalSubsidies;

    if (remainingAmount <= 0) {
      alert('No remaining amount to allocate');
      return;
    }

    const newSubsidy = {
      subsidy_id: Math.max(...donation.subsidies.map(s => s.subsidy_id)) + 1,
      user_id: '',
      amount: remainingAmount
    };

    const updatedDonations = donations.map(d => {
      if (d.donation_id === donationId) {
        return {
          ...d,
          subsidies: [...d.subsidies, newSubsidy]
        };
      }
      return d;
    });

    setDonations(updatedDonations);
    handleEditSubsidy(newSubsidy);
  };

  const handleDeleteSubsidy = (donationId, subsidyId) => {
    const updatedDonations = donations.map(donation => {
      if (donation.donation_id === donationId) {
        return {
          ...donation,
          subsidies: donation.subsidies.filter(s => s.subsidy_id !== subsidyId)
        };
      }
      return donation;
    });

    setDonations(updatedDonations);
  };

  return (
    <div className="w-full bg-white rounded-lg shadow p-6">
      <div className="pb-4">
        <h2 className="text-2xl font-bold">Chidon Donations Manager</h2>
      </div>
      <div className="overflow-x-auto">
        <table className="w-full border-collapse">
          <thead>
            <tr className="bg-gray-100">
              <th className="p-3 text-left border">Donation ID</th>
              <th className="p-3 text-left border">Donor</th>
              <th className="p-3 text-left border">Date</th>
              <th className="p-3 text-left border">Total Amount</th>
              <th className="p-3 text-left border">User ID</th>
              <th className="p-3 text-left border">Subsidy Amount</th>
              <th className="p-3 text-left border">Actions</th>
            </tr>
          </thead>
          <tbody>
            {donations.map(donation => (
              <React.Fragment key={donation.donation_id}>
                <tr className="bg-gray-50">
                  <td className="p-3 border" rowSpan={donation.subsidies.length + 1}>
                    {donation.donation_id}
                  </td>
                  <td className="p-3 border" rowSpan={donation.subsidies.length + 1}>
                    {donation.donor_name}
                  </td>
                  <td className="p-3 border" rowSpan={donation.subsidies.length + 1}>
                    {donation.date}
                  </td>
                  <td className="p-3 border" rowSpan={donation.subsidies.length + 1}>
                    ${donation.total_amount}
                  </td>
                  <td colSpan={3} className="p-3 border text-right">
                    <button
                      className="inline-flex items-center px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
                      onClick={() => handleAddSubsidy(donation.donation_id)}
                    >
                      <PlusCircle className="h-4 w-4 mr-2" />
                      Add Subsidy
                    </button>
                  </td>
                </tr>
                {donation.subsidies.map(subsidy => (
                  <tr key={subsidy.subsidy_id}>
                    <td className="p-3 border">
                      {editingSubsidyId === subsidy.subsidy_id ? (
                        <input
                          type="number"
                          value={editingValues.user_id}
                          onChange={(e) => setEditingValues({
                            ...editingValues,
                            user_id: e.target.value
                          })}
                          className="w-24 px-2 py-1 border rounded"
                        />
                      ) : (
                        subsidy.user_id
                      )}
                    </td>
                    <td className="p-3 border">
                      {editingSubsidyId === subsidy.subsidy_id ? (
                        <input
                          type="number"
                          value={editingValues.amount}
                          onChange={(e) => setEditingValues({
                            ...editingValues,
                            amount: e.target.value
                          })}
                          className="w-24 px-2 py-1 border rounded"
                        />
                      ) : (
                        `$${subsidy.amount}`
                      )}
                    </td>
                    <td className="p-3 border">
                      {editingSubsidyId === subsidy.subsidy_id ? (
                        <button
                          className="inline-flex items-center px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600"
                          onClick={() => handleSaveSubsidy(donation.donation_id, subsidy.subsidy_id)}
                        >
                          <Save className="h-4 w-4 mr-2" />
                          Save
                        </button>
                      ) : (
                        <div className="flex gap-2">
                          <button
                            className="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
                            onClick={() => handleEditSubsidy(subsidy)}
                          >
                            Edit
                          </button>
                          <button
                            className="inline-flex items-center px-2 py-1 text-red-500 hover:text-red-600"
                            onClick={() => handleDeleteSubsidy(donation.donation_id, subsidy.subsidy_id)}
                          >
                            <Trash2 className="h-4 w-4" />
                          </button>
                        </div>
                      )}
                    </td>
                  </tr>
                ))}
              </React.Fragment>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default DonationsManager;