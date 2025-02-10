const { useState, useEffect } = React;

function DonationManager() {
    const [donations, setDonations] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        fetchDonations();
    }, []);

    const fetchDonations = async () => {
        try {
            const response = await fetch('../ajax/getDonations.php');
            const data = await response.json();
            setDonations(data);
            setLoading(false);
        } catch (err) {
            setError('Failed to load donations');
            setLoading(false);
        }
    };

    const updateSubsidy = async (donationId, subsidyId, changes) => {
        try {
            const response = await fetch('../ajax/updateSubsidy.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    donationId,
                    subsidyId,
                    ...changes
                }),
            });
            
            if (!response.ok) throw new Error('Failed to update subsidy');
            
            // Refresh donations after update
            fetchDonations();
        } catch (err) {
            setError('Failed to update subsidy');
        }
    };

    if (loading) return <div>Loading...</div>;
    if (error) return <div className="alert alert-danger">{error}</div>;

    return (
        <div className="container mt-4">
            <h2>Chidon Donation Manager</h2>
            <table className="table table-striped">
                <thead>
                    <tr>
                        <th>Donation ID</th>
                        <th>Total Amount</th>
                        <th>Date</th>
                        <th>Subsidies</th>
                    </tr>
                </thead>
                <tbody>
                    {donations.map(donation => (
                        <tr key={donation.id}>
                            <td>{donation.id}</td>
                            <td>${donation.total_amount}</td>
                            <td>{new Date(donation.date).toLocaleDateString()}</td>
                            <td>
                                <table className="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>User ID</th>
                                            <th>Amount</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {donation.subsidies.map(subsidy => (
                                            <tr key={subsidy.id}>
                                                <td>
                                                    <input 
                                                        type="number" 
                                                        className="form-control form-control-sm"
                                                        value={subsidy.user_id}
                                                        onChange={(e) => updateSubsidy(
                                                            donation.id,
                                                            subsidy.id,
                                                            { user_id: e.target.value }
                                                        )}
                                                    />
                                                </td>
                                                <td>
                                                    <input 
                                                        type="number" 
                                                        className="form-control form-control-sm"
                                                        value={subsidy.amount}
                                                        onChange={(e) => updateSubsidy(
                                                            donation.id,
                                                            subsidy.id,
                                                            { amount: e.target.value }
                                                        )}
                                                    />
                                                </td>
                                                <td>
                                                    <button 
                                                        className="btn btn-danger btn-sm"
                                                        onClick={() => updateSubsidy(
                                                            donation.id,
                                                            subsidy.id,
                                                            { delete: true }
                                                        )}
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                        <tr>
                                            <td colSpan="3">
                                                <button 
                                                    className="btn btn-success btn-sm"
                                                    onClick={() => updateSubsidy(
                                                        donation.id,
                                                        null,
                                                        { 
                                                            user_id: '',
                                                            amount: 0,
                                                            new: true
                                                        }
                                                    )}
                                                >
                                                    Add Subsidy
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}

ReactDOM.render(<DonationManager />, document.getElementById('root'));